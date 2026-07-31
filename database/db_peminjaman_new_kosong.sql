DROP DATABASE IF EXISTS db_peminjaman;

CREATE DATABASE db_peminjaman
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE db_peminjaman;

=========================
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama_lengkap VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    level ENUM('Administrator','Petugas','Peminjam') NOT NULL,
    status ENUM('Aktif','Tidak Aktif') DEFAULT 'Aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

===============================
CREATE TABLE kategori (
    id_kategori INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

================================
CREATE TABLE alat (
    id_alat INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori INT NOT NULL,
    nama_alat VARCHAR(150) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    kondisi ENUM('Baik','Rusak Ringan','Rusak Berat') DEFAULT 'Baik',
    lokasi VARCHAR(100),
    foto VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_alat_kategori
    FOREIGN KEY(id_kategori)
    REFERENCES kategori(id_kategori)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

================================
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    status ENUM('Menunggu','Disetujui','Ditolak','Dipinjam','Selesai') DEFAULT 'Menunggu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_peminjaman_user
    FOREIGN KEY(id_user)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

===================================
CREATE TABLE detail_peminjaman (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL,
    id_alat INT NOT NULL,
    jumlah INT NOT NULL,

    CONSTRAINT fk_detail_peminjaman
    FOREIGN KEY(id_peminjaman)
    REFERENCES peminjaman(id_peminjaman)
    ON UPDATE CASCADE
    ON DELETE CASCADE,

    CONSTRAINT fk_detail_alat
    FOREIGN KEY(id_alat)
    REFERENCES alat(id_alat)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

=====================================
CREATE TABLE pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT NOT NULL UNIQUE,
    tanggal_pengembalian DATE NOT NULL,
    kondisi_kembali ENUM('Baik','Rusak Ringan','Rusak Berat') DEFAULT 'Baik',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT fk_pengembalian_peminjaman
    FOREIGN KEY(id_peminjaman)
    REFERENCES peminjaman(id_peminjaman)
    ON UPDATE CASCADE
    ON DELETE RESTRICT
) ENGINE=InnoDB;

======================================

CREATE TABLE detail_pengembalian (
    id_detail_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_pengembalian INT NOT NULL,
    id_alat INT NOT NULL,
    jumlah INT NOT NULL,
    kondisi ENUM('Baik','Rusak Ringan','Rusak Berat','Hilang') NOT NULL,
    keterangan TEXT NULL,

    CONSTRAINT fk_detail_pengembalian
        FOREIGN KEY (id_pengembalian)
        REFERENCES pengembalian(id_pengembalian)
        ON DELETE CASCADE,

    CONSTRAINT fk_detail_pengembalian_alat
        FOREIGN KEY (id_alat)
        REFERENCES alat(id_alat)
);

=======================================
CREATE TABLE log_aktivitas (
    id_log INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    aktivitas VARCHAR(255) NOT NULL,
    waktu DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),

    CONSTRAINT fk_log_user
    FOREIGN KEY(id_user)
    REFERENCES users(id_user)
    ON UPDATE CASCADE
    ON DELETE CASCADE
) ENGINE=InnoDB;