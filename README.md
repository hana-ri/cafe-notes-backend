## Cara setup
Pertama-tama, kita perlu membuat file .env berdasarkan dari file env.example, caranya jalankan perintah:

```
copy .env.example .env
```

Untuk pengguna Linux gunakan perintah:
```
cp .env.example .env
```


Berikutnya, kita instal package-package yang diinstal dalam composer di mana package tersebut akan disimpan dalam folder vendor. Jalankan perintah berikut di dalam command prompt atau terminal:

```
composer install
```

Setelah berhasil membuat file .env, berikutnya jalankan perintah berikut:

```
php artisan key:generate
```

Perintah ini akan meng-generate keyuntuk dimasukkan ke APP_KEY di file .env

Aplikasi Laravel tersebut memiliki database, buatlah nama database baru. Lalu sesuaikan nama database, username, dan password database di `file .env`.

Cari bagian 


```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

isi `DB_DATABASE=NamaDatabaseKalian` lalu `DB_USERNAME=UsernameDatabaseKalian` dan `DB_PASSWORD=passwordDatabaseKalian`.
Berikutnya jalankan perintah berikut:

```
php artisan migrate
```

Setelah itu jalankan perintah berikut untuk mengenerate key jwt.
```
php artisan jwt:secret
```

Terakhir, untuk membukanya di web browser, jalankan perintah:

```
php artisan serve
```