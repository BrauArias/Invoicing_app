export interface User {
    id: number;
    name: string;
    email: string;
    active_company_id: number | null;
}

export interface Company {
    id: number;
    name: string;
    trade_name?: string;
    nif: string;
    address?: string;
    city?: string;
    province?: string;
    postal_code?: string;
    country: string;
    email?: string;
    phone?: string;
    website?: string;
    iban?: string;
    swift?: string;
    logo_path?: string;
    invoice_template: 'classic' | 'modern' | 'minimal';
    primary_color: string;
    accent_color: string;
    default_vat_rate: number;
    irpf_rate: number;
    irpf_applicable: boolean;
    invoice_series: string;
    rectification_series: string;
    quote_series: string;
    invoice_counter: number;
    invoice_footer_text?: string;
    invoice_header_notes?: string;
    show_bank_details: boolean;
}

export interface Client {
    id: number;
    company_id: number;
    type: 'individual' | 'business';
    name: string;
    trade_name?: string;
    nif?: string;
    email?: string;
    phone?: string;
    website?: string;
    address?: string;
    city?: string;
    province?: string;
    postal_code?: string;
    country: string;
    vat_exempt: boolean;
    irpf_applicable: boolean;
    irpf_rate?: number;
    notes?: string;
    invoices_count?: number;
    total_invoiced?: number;
}

export interface Product {
    id: number;
    company_id: number;
    code?: string;
    name: string;
    description?: string;
    unit_price: number;
    vat_rate: number;
    unit: string;
    is_active: boolean;
}

export interface InvoiceLine {
    id?: number;
    product_id?: number | null;
    position: number;
    description: string;
    quantity: number;
    unit: string;
    unit_price: number;
    discount: number;
    vat_rate: number;
    subtotal: number;
    vat_amount: number;
    total: number;
}

export interface Invoice {
    id: number;
    company_id: number;
    client_id: number;
    client?: Client;
    series: string;
    number: number;
    full_number: string;
    type: 'invoice' | 'proforma' | 'quote' | 'credit_note';
    status: 'draft' | 'sent' | 'paid' | 'overdue' | 'cancelled';
    issue_date: string;
    due_date?: string;
    service_date?: string;
    client_name: string;
    client_nif?: string;
    client_address?: string;
    lines?: InvoiceLine[];
    subtotal: number;
    vat_amount: number;
    irpf_amount: number;
    total: number;
    payment_method?: string;
    payment_terms?: string;
    notes?: string;
    internal_notes?: string;
    currency: string;
    pdf_path?: string;
    paid_at?: string;
    created_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

export interface PageProps {
    auth: {
        user: User;
    };
    activeCompany: Company | null;
    companies: Pick<Company, 'id' | 'name' | 'logo_path' | 'invoice_series'>[];
    flash: {
        success?: string;
        error?: string;
    };
    [key: string]: unknown;
}
