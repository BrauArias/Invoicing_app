import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import ClientForm from './ClientForm';
import type { Client } from '@/types';

interface Props {
    client: Client;
}

export default function ClientEdit({ client }: Props) {
    const form = useForm({
        type: client.type,
        name: client.name,
        trade_name: client.trade_name ?? '',
        nif: client.nif ?? '',
        email: client.email ?? '',
        phone: client.phone ?? '',
        website: client.website ?? '',
        address: client.address ?? '',
        city: client.city ?? '',
        province: client.province ?? '',
        postal_code: client.postal_code ?? '',
        country: client.country,
        vat_exempt: client.vat_exempt,
        irpf_applicable: client.irpf_applicable,
        irpf_rate: String(client.irpf_rate ?? ''),
        notes: client.notes ?? '',
    });

    return (
        <AppLayout title={`Editar: ${client.name}`}>
            <Head title={`Editar cliente`} />
            <ClientForm
                form={form}
                onSubmit={() => form.put(`/clientes/${client.id}`)}
                submitLabel="Guardar cambios"
                backHref="/clientes"
            />
        </AppLayout>
    );
}
