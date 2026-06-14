# Sistema de Chamados Internos

Sistema web para gerenciamento de chamados internos de equipes. Permite criar, visualizar, editar e acompanhar chamados, com atribuição automática ou manual de responsável.

---

## Stack Tecnológica

| Camada | Tecnologia |
|---|---|
| Backend | PHP 8.4 + Laravel 13 |
| Frontend SPA | Vue 3 + Inertia.js v3 |
| Estilização | Tailwind CSS v4 |
| Banco de dados | SQLite (padrão) |
| Testes | Pest v4 |
| Análise estática | Larastan v3 (PHPStan) |

## Por que Laravel + Inertia + Vue?

- **Laravel** oferece convenções sólidas, Eloquent expressivo, Form Requests nativos e service container que facilitam separar regras de negócio do controller.
- **Inertia.js** elimina a necessidade de uma API REST separada: o servidor continua controlando as rotas e a autenticação, enquanto o cliente renderiza páginas como SPA — sem a complexidade de dois projetos distintos.
- **Vue 3** com Composition API permite componentes reativos e reutilizáveis com tipagem TypeScript de ponta a ponta.

---

## Decisões Arquiteturais

**Service layer:**
`TicketService` e `OwnerAssignmentService` isolam as regras de negócio do controller, que permanece fino (valida entrada, chama o serviço e redireciona).

**Enums PHP 8.1:**
`TicketStatus` e `TicketPriority` são backed enums tipados, eliminando strings mágicas e permitindo lógica no próprio tipo (ex.: `TicketStatus::isOpen()`).

**Rotas explícitas:**
`Route::resource('/')` gerava parâmetros vazios que quebravam o Wayfinder. Solução: rotas explícitas com nomes `tickets.*` e URLs na raiz (`/`, `/{ticket}`, etc.).

**Wayfinder:**
Gera funções TypeScript a partir das rotas nomeadas do Laravel (`@/routes/tickets`), garantindo que URLs no frontend sejam sempre sincronizadas com o backend.

**`opened_at` gerenciado pelo serviço:**
Registra quando o chamado entrou em estado aberto pela primeira vez. Definido pelo `TicketService` (não por observer) para manter a lógica explícita e testável. Nunca é sobrescrito após definido.

---

## Status dos Chamados

| Valor | Label | Descrição |
|---|---|---|
| `open` | Aberto | Registrado, aguardando atendimento |
| `in_progress` | Em Andamento | Em tratamento ativo |
| `resolved` | Resolvido | Solução aplicada, aguardando confirmação |
| `closed` | Fechado | Encerrado definitivamente |

## O que é "Chamado em Aberto"?

Para fins de **distribuição automática de carga**, um chamado é considerado *em aberto* quando seu status é `open` **ou** `in_progress`. Chamados `resolved` e `closed` não contam para a carga do responsável.

## Regra de Distribuição Automática

Ao criar ou reatribuir um chamado sem especificar responsável:

1. Conta os chamados em aberto (`open` + `in_progress`) de cada responsável cadastrado
2. Atribui ao responsável com **menor** contagem
3. Em empate, vence o responsável com **menor ID** (determinístico e previsível)
4. Se não houver responsáveis cadastrados, retorna erro ao usuário

---

## Requisitos

- PHP ^8.4
- Composer ^2
- Node.js ^20
- SQLite (padrão) ou MySQL 8+ / PostgreSQL 15+

---

## Instalação

```bash
git clone <url-do-repositorio>
cd codificar-teste-tecnico

composer install
npm install

cp .env.example .env
php artisan key:generate
```

## Configuração do .env

O projeto usa **SQLite por padrão** — sem necessidade de configurar servidor de banco.

```env
DB_CONNECTION=sqlite
```

Para MySQL ou PostgreSQL, ajuste as variáveis `DB_*`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=chamados
DB_USERNAME=root
DB_PASSWORD=
```

## Banco de Dados

```bash
# SQLite: criar o arquivo (se não existir)
touch database/database.sqlite

# Executar migrations
php artisan migrate

# Executar seeders (cria 3 responsáveis padrão)
php artisan db:seed
```

**Responsáveis criados pelo seed:**

| Nome | E-mail |
|---|---|
| João Silva | joao@empresa.com |
| Maria Souza | maria@empresa.com |
| Pedro Santos | pedro@empresa.com |

---

## Rodando a Aplicação

```bash
# Servidor + Vite em paralelo (recomendado)
composer run dev

# Ou separadamente:
php artisan serve   # backend em http://localhost:8000
npm run dev         # Vite com hot reload
```

Acesse: `http://localhost:8000`

## Build de Produção

```bash
npm run build
php artisan optimize
```

---

## Testes

```bash
# Suite completa
php artisan test

# Saída compacta
php artisan test --compact

# Filtrar por arquivo ou nome
php artisan test --filter=TicketHttpTest
php artisan test --filter=TicketServiceTest
```

**Cobertura:**

| Arquivo | Tipo | Foco |
|---|---|---|
| `TicketTest.php` | Feature | Model: `isOpen()`, `scopeOpen`, casts |
| `TicketServiceTest.php` | Feature | Serviços: auto-assign, tiebreaker, `opened_at` |
| `TicketHttpTest.php` | Feature | Controller HTTP: CRUD, filtros, atribuição |

---

## Bibliotecas Externas

| Pacote | Versão | Finalidade |
|---|---|---|
| `inertiajs/inertia-laravel` | v3 | Adaptador server-side do Inertia |
| `@inertiajs/vue3` | v3 | Cliente Inertia para Vue |
| `laravel/wayfinder` | v0 | Geração de rotas tipadas para TypeScript |
| `pestphp/pest` | v4 | Framework de testes com sintaxe expressiva |
| `larastan/larastan` | v3 | Análise estática PHPStan para Laravel |
| `laravel/pint` | v1 | Formatador PHP (PSR-12) |
| `tailwindcss` | v4 | Framework CSS utilitário |

---

## Trade-offs e Melhorias Futuras

**Trade-offs adotados:**

- **SQLite como padrão** — zero configuração local; troca trivial para MySQL/PostgreSQL via `.env`
- **Rotas explícitas no root** — necessário para compatibilidade com Wayfinder; sem impacto funcional
- **Sem autenticação** — fora do escopo do desafio; estrutura pronta para adicionar Laravel Sanctum ou Fortify
- **`opened_at` no serviço** — explícito e testável; alternativa via observer adicionaria indireção desnecessária

**Possíveis evoluções:**

- Autenticação com papéis (admin / responsável)
- Comentários e histórico de mudanças de status por chamado
- Notificações por e-mail ao atribuir responsável
- SLA com alertas de prazo vencendo
- Dashboard com métricas de carga por responsável
- API REST versionada para integrações externas
