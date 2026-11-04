/**
 * EventTix - Interaksi Klien Minimal
 * Perhitungan harga resmi tetap dieksekusi di backend PHP.
 */
document.addEventListener('DOMContentLoaded', () => {
  // 1. Fokus otomatis pada kolom kode tiket check-in
  const checkinInput = document.getElementById('input-kode-checkin');
  if (checkinInput) {
    checkinInput.focus();
    checkinInput.select();
  }

  // 2. Interaksi Pemilihan Tiket & Kalkulasi Tampilan Checkout
  const ticketSelectInputs = document.querySelectorAll('.ticket-option-input');
  const subtotalDisplay = document.getElementById('display-subtotal');
  const promoDisplay = document.getElementById('display-promo');
  const adminDisplay = document.getElementById('display-admin');
  const totalDisplay = document.getElementById('display-total');
  const paymentRadios = document.querySelectorAll('input[name="metode_pembayaran"]');
  const qtyInput = document.getElementById('ticket-qty');

  function updatePriceSummary() {
    if (!subtotalDisplay) return;

    let subtotal = 0;
    let discount = 0;

    // Jika pada langkah 1 (memilih kategori secara langsung via radio)
    if (ticketSelectInputs.length > 0) {
      const selectedOption = document.querySelector('.ticket-option-input:checked');
      if (selectedOption) {
        const price = parseFloat(selectedOption.getAttribute('data-price') || 0);
        const qty = qtyInput ? parseInt(qtyInput.value, 10) || 1 : 1;
        subtotal = price * qty;
      }
      
      const activeDiscountElem = document.getElementById('active-discount-val');
      if (activeDiscountElem) {
        const rate = parseFloat(activeDiscountElem.getAttribute('data-rate') || 0);
        const maxDiscount = parseFloat(activeDiscountElem.getAttribute('data-max') || 0);
        discount = Math.min(subtotal * rate, maxDiscount);
      }
    } else {
      // Pada langkah 2 atau 3, ambil nilai subtotal resmi dari atribut data yang dirender PHP
      subtotal = parseFloat(subtotalDisplay.getAttribute('data-subtotal') || 0);
      discount = parseFloat(promoDisplay ? promoDisplay.getAttribute('data-discount') || 0 : 0);
    }

    // Biaya admin berdasarkan metode pembayaran
    let adminFee = 2500; // default transfer
    const selectedPayment = document.querySelector('input[name="metode_pembayaran"]:checked');
    if (selectedPayment) {
      if (selectedPayment.value === 'ewallet') {
        adminFee = 1000;
      } else {
        adminFee = 2500;
      }
    }

    const total = Math.max(0, subtotal - discount + adminFee);

    if (ticketSelectInputs.length > 0 && subtotalDisplay) {
      subtotalDisplay.textContent = formatRupiah(subtotal);
      if (promoDisplay) promoDisplay.textContent = discount > 0 ? '- ' + formatRupiah(discount) : 'Rp 0';
    }
    if (adminDisplay) adminDisplay.textContent = formatRupiah(adminFee);
    if (totalDisplay) totalDisplay.textContent = formatRupiah(total);
  }

  ticketSelectInputs.forEach(input => {
    input.addEventListener('change', updatePriceSummary);
  });

  paymentRadios.forEach(radio => {
    radio.addEventListener('change', updatePriceSummary);
  });

  if (qtyInput) {
    qtyInput.addEventListener('input', updatePriceSummary);
  }

  function formatRupiah(amount) {
    return 'Rp ' + Math.round(amount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  // Sinkronisasi awal
  if (subtotalDisplay) {
    updatePriceSummary();
  }
});
