import React, { useEffect, useMemo, useState } from 'react';

const emptyDetails = { name: '', email: '', phone: '', answers: {} };

function PaymentChoices({ methods, order, csrf }) {
    const [selectedCode, setSelectedCode] = useState(methods[0]?.code || '');
    const [uploading, setUploading] = useState(false);
    const [proofMessage, setProofMessage] = useState('');
    const [proofError, setProofError] = useState('');
    const selected = methods.find(method => method.code === selectedCode);

    const submitProof = async event => {
        event.preventDefault();
        const form = event.currentTarget;
        setUploading(true);
        setProofError('');
        setProofMessage('');

        try {
            const response = await fetch(`/pemesanan/${order.uuid}/bukti-pembayaran`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
                body: new FormData(form),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Bukti pembayaran belum dapat dikirim.');
            }

            setProofMessage(data.message);
            form.reset();
        } catch (exception) {
            setProofError(exception.message);
        } finally {
            setUploading(false);
        }
    };

    if (!methods.length) {
        return <p className="order-payment-empty">Belum ada metode pembayaran aktif. Silakan hubungi admin.</p>;
    }

    return <div className="order-payment">
        <p className="order-label">Pilih metode pembayaran</p>
        <div className="order-payment-options">
            {methods.map(method => <button type="button" className={selectedCode === method.code ? 'is-selected' : ''} onClick={() => setSelectedCode(method.code)} key={method.id}>
                <i className={`fa-solid ${method.is_qris ? 'fa-qrcode' : 'fa-building-columns'}`}/>
                <span>{method.name}<small>{method.is_qris ? 'Pembayaran otomatis via QRIS' : `Transfer ke ${method.account_number}`}</small></span>
                <i className="fa-solid fa-check"/>
            </button>)}
        </div>
        {selected && <div className="order-payment-detail">
            {selected.is_qris ? <>
                <p>QRIS akan dibuat berdasarkan total pesanan dan berlaku selama 15 menit.</p>
                <form method="POST" action="/payment/dana/generate" target="_blank" onSubmit={event => { const button = event.currentTarget.querySelector('button'); button.disabled = true; setTimeout(() => { button.disabled = false; }, 5000); }}>
                    <input type="hidden" name="_token" value={csrf}/><input type="hidden" name="order_uuid" value={order.uuid}/>
                    <button type="submit" className="button-primary">Generate QRIS <i className="fa-solid fa-qrcode"/></button>
                </form>
            </> : <>
                <dl><div><dt>Atas nama</dt><dd>{selected.account_name}</dd></div><div><dt>Nomor rekening/akun</dt><dd>{selected.account_number}</dd></div></dl>
                {selected.instructions && <p>{selected.instructions}</p>}
                <p className="order-payment-note"><i className="fa-solid fa-circle-info"/> Setelah transfer, unggah bukti pembayaran di sini. Admin akan memeriksa dan memperbarui status pembayaran Anda.</p>
                <form onSubmit={submitProof} encType="multipart/form-data">
                    <input type="hidden" name="payment_method_id" value={selected.id}/>
                    <label htmlFor="payment-proof" className="block text-xs font-semibold text-[#582308]">Bukti pembayaran (JPG, PNG, WebP, atau PDF; maks. 5 MB)</label>
                    <input id="payment-proof" name="proof" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf" required className="mt-2 block w-full rounded-xl border border-[#582308]/10 bg-white px-3 py-2 text-xs"/>
                    {proofError && <p className="order-error">{proofError}</p>}
                    {proofMessage && <p className="order-success"><i className="fa-solid fa-circle-check"/>{proofMessage}</p>}
                    <button type="submit" disabled={uploading} className="order-submit">{uploading ? 'Mengirim bukti...' : 'Unggah bukti pembayaran'}<i className="fa-solid fa-arrow-up-from-bracket"/></button>
                </form>
            </>}
        </div>}
    </div>;
}

export default function OrderSection() {
    const [catalogs, setCatalogs] = useState([]);
    const [paymentMethods, setPaymentMethods] = useState([]);
    const [selected, setSelected] = useState(null);
    const [template, setTemplate] = useState(null);
    const [details, setDetails] = useState(emptyDetails);
    const [savedOrder, setSavedOrder] = useState(null);
    const [loading, setLoading] = useState(true);
    const [sending, setSending] = useState(false);
    const [editing, setEditing] = useState(false);
    const [error, setError] = useState('');
    const [search, setSearch] = useState('');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    useEffect(() => {
        Promise.all([
            fetch('/pemesanan/katalog', { headers: { Accept: 'application/json' } }).then(response => response.ok ? response.json() : Promise.reject()),
            fetch('/pemesanan/metode-pembayaran', { headers: { Accept: 'application/json' } }).then(response => response.ok ? response.json() : Promise.reject()),
        ]).then(([catalogData, methodData]) => { setCatalogs(catalogData); setPaymentMethods(methodData); }).catch(() => setError('Data pemesanan belum dapat dimuat.')).finally(() => setLoading(false));
        const choosePackage = event => { const link = event.target.closest('.pricing-cta'); if (!link) return; event.preventDefault(); setSearch(link.closest('.pricing-card')?.querySelector('.pricing-plan')?.textContent?.trim() || ''); document.getElementById('mulai-pemesanan')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); };
        document.addEventListener('click', choosePackage);
        return () => document.removeEventListener('click', choosePackage);
    }, []);

    const visibleCatalogs = useMemo(() => { const query = search.trim().toLowerCase(); return catalogs.filter(catalog => `${catalog.name} ${catalog.category} ${catalog.package}`.toLowerCase().includes(query)); }, [catalogs, search]);
    const choose = async catalog => { setSelected(catalog); setTemplate(null); setSavedOrder(null); setEditing(false); setDetails(emptyDetails); setError(''); document.getElementById('mulai-pemesanan')?.scrollIntoView({ behavior: 'smooth', block: 'start' }); try { if (!catalog.package_id) throw new Error('Paket aktif untuk desain ini belum tersedia.'); const response = await fetch(`/pemesanan/katalog/${catalog.id}/form`, { headers: { Accept: 'application/json' } }); const data = await response.json(); if (!data.template) throw new Error('Form untuk kategori ini belum tersedia.'); setTemplate(data.template); } catch (exception) { setError(exception.message); } };
    const submit = async event => { event.preventDefault(); setSending(true); setError(''); try { const response = await fetch(savedOrder ? `/pemesanan/${savedOrder.uuid}` : '/pemesanan', { method: savedOrder ? 'PATCH' : 'POST', headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ catalog_id: selected.id, package_id: selected.package_id, ...details }) }); const data = await response.json(); if (!response.ok) throw new Error(data.message || Object.values(data.errors || {})[0]?.[0] || 'Data belum dapat disimpan.'); setSavedOrder(data.order); setEditing(false); } catch (exception) { setError(exception.message); } finally { setSending(false); } };
    const setValue = (key, value) => setDetails(current => ({ ...current, [key]: value }));
    const setAnswer = (key, value) => setDetails(current => ({ ...current, answers: { ...current.answers, [key]: value } }));

    return <section id="pemesanan" className="order-section"><div id="mulai-pemesanan" className="mx-auto max-w-7xl scroll-mt-28 px-5 lg:px-8">
        <div className="order-heading"><div><p className="eyebrow"><span/>Mulai pemesanan</p><h2>Pilih desain, lalu<br/><em>ceritakan momennya.</em></h2></div><p>Isi detail pesanan, kemudian pilih metode pembayaran aktif yang paling nyaman.</p></div>
        <div className="order-layout"><div className="order-catalog-list"><p className="order-label">01 · Pilih koleksi</p><input type="search" value={search} onChange={event => setSearch(event.target.value)} placeholder="Cari desain atau paket" className="mb-3 h-11 w-full rounded-xl border border-[#582308]/10 bg-[#faf7f0] px-4 text-sm outline-none"/>{loading ? <p className="order-muted">Memuat koleksi...</p> : visibleCatalogs.map(catalog => <button type="button" onClick={() => choose(catalog)} className={`order-catalog ${selected?.id === catalog.id ? 'is-selected' : ''}`} key={catalog.id} style={{ '--catalog-color': catalog.color || '#582308' }}><span className="order-catalog-art" style={catalog.image ? { backgroundImage: `linear-gradient(rgba(42,20,10,.25),rgba(42,20,10,.55)), url(${catalog.image})` } : undefined}><b>{catalog.name.slice(0, 1)}</b></span><span><small>{catalog.category}</small><strong>{catalog.name}</strong><em>{catalog.package}</em></span><i className="fa-solid fa-arrow-right"/></button>)}{!loading && !visibleCatalogs.length && <p className="order-muted">Koleksi tidak ditemukan.</p>}</div>
            <div className="order-form-wrap">{!selected ? <div className="order-placeholder"><i className="fa-regular fa-pen-to-square"/><h3>Detail pesanan akan dimulai di sini.</h3><p>Pilih salah satu desain dari koleksi di sebelah kiri untuk membuka formulir sesuai kategorinya.</p></div> : !template ? <div className="order-placeholder"><i className={`fa-solid ${error ? 'fa-circle-exclamation' : 'fa-spinner fa-spin'}`}/><h3>{error ? 'Formulir belum dapat dibuka' : 'Menyiapkan formulir'}</h3><p>{error || 'Mohon tunggu sebentar.'}</p></div> : savedOrder && !editing ? <div className="order-saved-summary"><div className="order-success"><i className="fa-solid fa-circle-check"/>Data pemesan berhasil disimpan</div><h3>{selected.name}</h3><dl><div><dt>Nama</dt><dd>{savedOrder.name}</dd></div><div><dt>WhatsApp</dt><dd>{savedOrder.phone}</dd></div><div><dt>Email</dt><dd>{savedOrder.email}</dd></div><div><dt>Paket</dt><dd>{savedOrder.package}</dd></div><div><dt>Total</dt><dd>Rp {Number(savedOrder.total).toLocaleString('id-ID')}</dd></div></dl><div className="order-actions"><button type="button" onClick={() => setEditing(true)} className="button-secondary">Edit Data</button></div><PaymentChoices methods={paymentMethods} order={savedOrder} csrf={csrf}/></div> : <form className="public-order-form" onSubmit={submit}><div className="order-package-summary"><div><p className="order-label">02 · Data pemesanan</p><h3>{selected.name}</h3><p>{selected.package} · Rp {Number(selected.price).toLocaleString('id-ID')}</p></div>{selected.image && <img src={selected.image} alt=""/>}</div>{error && <div className="order-error">{error}</div>}<div className="order-personal"><label>Nama lengkap *<input required maxLength="150" value={details.name} onChange={event => setValue('name', event.target.value)}/></label><label>Email *<input required type="email" maxLength="255" value={details.email} onChange={event => setValue('email', event.target.value)}/></label><label>WhatsApp *<input required maxLength="30" placeholder="08xxxxxxxxxx" value={details.phone} onChange={event => setValue('phone', event.target.value)}/></label></div><div className="order-fields">{template.fields.map(field => <label key={field.key}>{field.label}{field.required && <b>*</b>}{field.type === 'textarea' ? <textarea required={field.required} maxLength="2000" value={details.answers[field.key] || ''} onChange={event => setAnswer(field.key, event.target.value)}/> : field.type === 'select' ? <select required={field.required} value={details.answers[field.key] || ''} onChange={event => setAnswer(field.key, event.target.value)}><option value="">Pilih salah satu</option>{field.options.map(option => <option value={option} key={option}>{option}</option>)}</select> : <input required={field.required} type={field.type} maxLength="2000" value={details.answers[field.key] || ''} onChange={event => setAnswer(field.key, event.target.value)}/>}</label>)}</div><button className="order-submit" disabled={sending}>{sending ? 'Menyimpan data...' : 'Simpan Data'}<i className="fa-solid fa-arrow-right"/></button></form>}</div>
        </div>
    </div></section>;
}
