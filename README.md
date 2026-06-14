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

**`opened_at` e `closed_at` gerenciados pelo serviço:**
Registram, respectivamente, quando o chamado entrou em estado aberto e quando foi fechado/resolvido pela primeira vez. Ambos são definidos pelo `TicketService` e nunca sobrescritos após definidos. `created_at`/`updated_at` são campos internos do Laravel e não são exibidos na interface.

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
- SQLite (padrão)

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

## Banco de Dados

```bash
# SQLite: criar o arquivo (se não existir)
touch database/database.sqlite

# Executar migrations + seeders
php artisan migrate --seed
```

**Dados criados pelo seed:**

3 responsáveis padrão (João Silva, Maria Souza, Pedro Santos) e 5 chamados de exemplo:

| Chamado | Prioridade | Status |
|---|---|---|
| Erro ao emitir relatório financeiro mensal | Alta | Aberto |
| Usuário não consegue acessar o portal interno | Média | Em Andamento |
| Solicitação de instalação de impressora | Baixa | Resolvido |
| Sistema de vendas apresenta lentidão | Alta | Em Andamento |
| Atualização de dados cadastrais de colaborador | Baixa | Fechado |

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
| `TicketServiceTest.php` | Feature | Serviços: auto-assign, tiebreaker, `opened_at`, `closed_at` |
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
