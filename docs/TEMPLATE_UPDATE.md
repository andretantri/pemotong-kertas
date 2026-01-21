# Update Template DashboardKit

## 📋 Perubahan Template

Aplikasi HMI Pemotong Kertas telah diupdate menggunakan template **DashboardKit Free Admin Template** dengan desain modern dan profesional.

## 🎨 Fitur Template Baru

### 1. Layout dengan Sidebar
- **Sidebar Navigation** dengan gradient purple
- Menu navigasi yang jelas dan mudah digunakan
- Responsive untuk mobile (sidebar dapat di-toggle)

### 2. Topbar
- Header dengan judul halaman
- User info display
- Tombol logout yang mudah diakses
- Sticky position untuk selalu terlihat

### 3. Statistics Cards
- 4 kartu statistik dengan gradient warna:
  - **Total Pekerjaan** (Primary - Purple)
  - **Selesai** (Success - Green)
  - **Berjalan** (Warning - Orange)
  - **Total Potong** (Danger - Red)
- Icon yang jelas dan nilai yang mudah dibaca

### 4. Modern Card Design
- Card dengan shadow dan border radius
- Card header dengan icon
- Card body yang rapi
- Hover effects yang smooth

### 5. Enhanced Buttons
- Tombol START (hijau) dengan hover effect
- Tombol STOP (merah) dengan hover effect
- Animasi transform pada hover
- Shadow effects

### 6. Improved Tables
- Table dengan header yang jelas
- Hover effects pada rows
- Badge untuk status dengan warna yang sesuai

### 7. Responsive Design
- Mobile-friendly dengan sidebar toggle
- Layout yang adaptif untuk berbagai ukuran layar
- Touch-friendly buttons untuk mobile

## 📁 File Baru

### Assets Folder
```
assets/
├── css/
│   └── dashboardkit.css    # Custom CSS untuk DashboardKit
└── js/
    └── dashboardkit.js     # Custom JavaScript
```

## 🔄 Perubahan File

### 1. `login.php`
- ✅ Menggunakan class `login-page` dari DashboardKit
- ✅ Login card dengan header gradient
- ✅ Styling yang lebih modern

### 2. `dashboard.php`
- ✅ Layout dengan sidebar dan topbar
- ✅ Statistics cards dengan gradient
- ✅ Enhanced control panel
- ✅ Improved status display
- ✅ Modern table design

### 3. `log.php`
- ✅ Layout dengan sidebar dan topbar
- ✅ Enhanced filter section
- ✅ Improved table design
- ✅ Better pagination

## 🎨 Color Scheme

Template menggunakan color scheme yang konsisten:

- **Primary:** `#5e72e4` (Purple)
- **Success:** `#2dce89` (Green)
- **Danger:** `#f5365c` (Red)
- **Warning:** `#fb6340` (Orange)
- **Dark:** `#172b4d` (Dark Blue)
- **Light:** `#f7fafc` (Light Gray)

## 📱 Responsive Breakpoints

- **Desktop:** > 768px (Sidebar selalu terlihat)
- **Mobile:** ≤ 768px (Sidebar dapat di-toggle)

## 🚀 Cara Menggunakan

### 1. Pastikan Assets Ter-load
Semua file CSS dan JS sudah di-include di setiap halaman:
```html
<link rel="stylesheet" href="assets/css/dashboardkit.css">
<script src="assets/js/dashboardkit.js"></script>
```

### 2. Struktur HTML
Setiap halaman menggunakan struktur:
```html
<aside class="sidebar">...</aside>
<div class="main-content">
    <header class="topbar">...</header>
    <div class="content-wrapper">...</div>
</div>
```

### 3. Navigation
Sidebar navigation menggunakan class `nav-link` dengan `active` untuk halaman aktif:
```html
<a class="nav-link active" href="dashboard.php">Dashboard</a>
```

## 🎯 Fitur JavaScript

### Auto-refresh
Dashboard secara otomatis refresh setiap 2 detik untuk update status real-time.

### Sidebar Toggle
Pada mobile, sidebar dapat di-toggle dengan tombol di topbar.

### Alert Modal
Function `showAlert()` untuk menampilkan notifikasi dengan modal Bootstrap.

## 🔧 Customization

### Mengubah Warna
Edit file `assets/css/dashboardkit.css` dan ubah variabel CSS:
```css
:root {
    --primary-color: #5e72e4;
    --success-color: #2dce89;
    --danger-color: #f5365c;
    /* ... */
}
```

### Mengubah Sidebar Width
```css
:root {
    --sidebar-width: 260px; /* Default */
}
```

### Menambah Menu Sidebar
Edit file `dashboard.php` atau `log.php`:
```html
<li class="nav-item">
    <a class="nav-link" href="new-page.php">
        <i class="bi bi-icon-name"></i>
        <span>Menu Baru</span>
    </a>
</li>
```

## 📊 Komponen yang Tersedia

### Cards
- `.card` - Basic card
- `.card-header` - Card header
- `.card-body` - Card body
- `.stats-card` - Statistics card dengan gradient

### Buttons
- `.btn-start` - Tombol start (hijau)
- `.btn-stop` - Tombol stop (merah)
- `.btn-logout` - Tombol logout

### Badges
- `.badge.bg-primary` - Primary badge
- `.badge.bg-success` - Success badge
- `.badge.bg-danger` - Danger badge
- `.badge.bg-warning` - Warning badge

### Tables
- `.table` - Basic table dengan styling
- Hover effects otomatis

## ✅ Checklist Update

- [x] File CSS DashboardKit dibuat
- [x] File JS DashboardKit dibuat
- [x] Login page diupdate
- [x] Dashboard page diupdate
- [x] Log page diupdate
- [x] Sidebar navigation ditambahkan
- [x] Topbar ditambahkan
- [x] Statistics cards ditambahkan
- [x] Responsive design diimplementasikan
- [x] Mobile sidebar toggle ditambahkan

## 🎉 Hasil

Aplikasi sekarang memiliki:
- ✅ Tampilan yang lebih modern dan profesional
- ✅ Navigation yang lebih mudah digunakan
- ✅ Statistics yang jelas dan informatif
- ✅ Responsive untuk semua device
- ✅ User experience yang lebih baik

---

**Versi Template:** DashboardKit Free Admin Template  
**Update Date:** 2024

