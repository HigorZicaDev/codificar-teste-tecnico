export type TicketPriority = 'low' | 'medium' | 'high';
export type TicketStatus = 'open' | 'in_progress' | 'resolved' | 'closed';

export type Owner = {
    id: number;
    name: string;
    email: string;
};

export type Ticket = {
    id: number;
    title: string;
    description: string;
    priority: TicketPriority;
    status: TicketStatus;
    owner_id: number;
    owner: Owner;
    opened_at: string | null;
    closed_at: string | null;
    created_at: string;
    updated_at: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginatedTickets = {
    data: Ticket[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
};

export type TicketFilters = {
    search?: string;
    status?: TicketStatus | '';
    priority?: TicketPriority | '';
    owner_id?: number | '';
};
