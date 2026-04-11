import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import ClientForm from './ClientForm';

export default function ClientCreate() {
    const form = useForm({
        type: 'business' as 'individual' | 'business',
        name: '',
        trade_name: '',
        nif: '',
        email: '',
        phone: '',
        website: '',
        address: '',
        city: '',
        province: '',
        postal_code: '',
        country: 'ES',
        vat_exempt: false,
        irpf_applicable: false,
        irpf_rate: '',
        notes: '',
    });

    return (
        <AppLayout title="Nuevo cliente">
            <Head title="Nuevo cliente" />
            <ClientForm
                form={form}
                onSubmit={() => form.post('/clientes')}
                submitLabel="Crear cliente"
                backHref="/clientes"
            />
        </AppLayout>
    );
}
