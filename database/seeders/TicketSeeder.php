<?php

namespace Database\Seeders;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Services\TicketService;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function __construct(private TicketService $ticketService) {}

    public function run(): void
    {
        $tickets = [
            [
                'title' => 'Erro ao emitir relatório financeiro mensal',
                'description' => 'Ao gerar o relatório financeiro referente ao mês atual, o sistema retorna erro 500 após alguns segundos de processamento. O problema ocorre para diferentes usuários.',
                'priority' => TicketPriority::High,
                'status' => TicketStatus::Open,
            ],
            [
                'title' => 'Usuário não consegue acessar o portal interno',
                'description' => 'Após redefinir a senha, o colaborador informa que continua recebendo mensagem de credenciais inválidas ao tentar acessar o portal.',
                'priority' => TicketPriority::Medium,
                'status' => TicketStatus::InProgress,
            ],
            [
                'title' => 'Solicitação de instalação de impressora',
                'description' => 'A equipe administrativa precisa configurar uma nova impressora de rede na sala de reuniões do segundo andar.',
                'priority' => TicketPriority::Low,
                'status' => TicketStatus::Resolved,
            ],
            [
                'title' => 'Sistema de vendas apresenta lentidão',
                'description' => 'Usuários relatam demora superior a 20 segundos para carregar pedidos e consultar histórico de clientes durante horários de pico.',
                'priority' => TicketPriority::High,
                'status' => TicketStatus::InProgress,
            ],
            [
                'title' => 'Atualização de dados cadastrais de colaborador',
                'description' => 'Solicitada atualização do departamento e centro de custo de um colaborador transferido para outra área da empresa.',
                'priority' => TicketPriority::Low,
                'status' => TicketStatus::Closed,
            ],
        ];

        foreach ($tickets as $data) {
            $this->ticketService->create($data);
        }
    }
}
