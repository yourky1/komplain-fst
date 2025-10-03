-- Database: komplain_fst
CREATE DATABASE IF NOT EXISTS komplain_fst;
USE komplain_fst;

-- Table: users
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nim VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    jurusan VARCHAR(50) NOT NULL,
    no_telp VARCHAR(15),
    role ENUM('mahasiswa', 'admin') DEFAULT 'mahasiswa',
    foto_profil VARCHAR(255) DEFAULT 'default.jpg',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: komplain
CREATE TABLE IF NOT EXISTS komplain (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    kategori ENUM('akademik', 'fasilitas', 'administrasi', 'dosen', 'lainnya') NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lampiran VARCHAR(255),
    status ENUM('pending', 'diproses', 'selesai', 'ditolak') DEFAULT 'pending',
    prioritas ENUM('rendah', 'sedang', 'tinggi') DEFAULT 'sedang',
    tanggal_submit TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: tanggapan
CREATE TABLE IF NOT EXISTS tanggapan (
    id INT PRIMARY KEY AUTO_INCREMENT,
    komplain_id INT NOT NULL,
    user_id INT NOT NULL,
    pesan TEXT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (komplain_id) REFERENCES komplain(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    komplain_id INT,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (komplain_id) REFERENCES komplain(id) ON DELETE SET NULL
);

-- Insert default admin
INSERT INTO users (nim, nama, email, password, jurusan, role) VALUES
('ADMIN001', 'Administrator FST', 'admin@fst.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 'admin');
-- Password default: password

-- Sample mahasiswa
INSERT INTO users (nim, nama, email, password, jurusan, no_telp) VALUES
('2021001', 'Ahmad Fauzi', 'ahmad@fst.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Teknik Informatika', '081234567890'),
('2021002', 'Siti Nurhaliza', 'siti@fst.ac.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sistem Informasi', '081234567891');