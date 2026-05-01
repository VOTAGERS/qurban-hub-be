Journey mekanisme Checkout (Stripe Like)

Di tampilan Checkout Internal ada 2 pilihan Pilihan produk
1. User klik Produk (misal sapi)
2. tampil form Untuk pembelian
  - FORM Billings
  - FORM Shipping (ini bisa sama dengan billing)
  - FORM Recipients (ini sesuai dengan produk yg dipilih, tampilan max_share)

- Setelah user klik proceed to payment Data disimpan menggunakan db transaction
  - simpan data order untuk dapatkan id_order
  - simpan data user berdasarkan bilings dan shipping, jika user sama maka simpan 1 user, jika beda maka simpan 2 user
  - tambahkan data id_user yg ada di table order menggunakan data user yg billings tadi
  - lalu simpan id_user dan id_order pada table billing dan shipping
  - simpan data recipients ke table order_participants sesuai dengan id_order (proses looping berdasarkan jumlah recipients)
  - simpan data order di table app_logs