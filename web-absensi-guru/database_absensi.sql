CREATE DATABASE absensi_guru;
USE absensi_guru;

CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nip VARCHAR(20) UNIQUE NOT NULL,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('guru', 'admin') DEFAULT 'guru',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE absensi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    nip VARCHAR(20) NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME,
    jam_keluar TIME,
    status ENUM('hadir', 'telat', 'izin', 'alfa') DEFAULT 'hadir',
    keterangan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_absensi (nip, tanggal)
);

-- Insert admin default
INSERT INTO users (nip, nama, email, password, role) VALUES 
('ADMIN001', 'Administrator', 'admin@sekolah.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert guru sample
INSERT INTO users (nip, nama, email, password, role) VALUES 
('196701121994031001', 'Budi Santoso', 'budi@sekolah.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru'),
('197802251998021002', 'Siti Aminah', 'siti@sekolah.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'guru');