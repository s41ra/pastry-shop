# Artisan Pastry Shop - PHP & MySQL Web Application

A beautiful, fully-functional pastry shop website with user authentication and CRUD operations for managing pastries.

## Features

### Pages
1. **Landing Page** - Featured pastries showcase with warm, inviting design
2. **Login Page** - User authentication with session management
3. **Sign Up Page** - New user registration with validation
4. **Admin Dashboard** - Complete pastry management system

### CRUD Operations
- **Create** - Add new pastries with image, description, price, category
- **Read** - View all pastries on homepage and dashboard
- **Update** - Edit existing pastry details
- **Delete** - Remove pastries with confirmation

### Design Highlights
- Warm pastry-themed color palette (creams, browns, raspberry, honey)
- Custom typography using Playfair Display and Quattrocento
- Smooth animations and hover effects
- Responsive grid layouts
- Background textures and gradients
- Interactive form validation

## Setup Instructions

### 1. Database Setup


1. Open http://localhost/pastry-shop/
2. Done it is automatic create database and tables 


```

### 2. Configure Database Connection

Edit `includes/config.php` if your database settings differ:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');          // Your MySQL username
define('DB_PASS', '');              // Your MySQL password
define('DB_NAME', 'pastry_shop');
```


## File Structure

```
pastry-shop/
├── admin/
│   ├── admin_dashboard.php # Main admin dashboard
│   ├── inventory.php      # Inventory management
│   ├── inventory_actions.php # API for inventory CRUD
│   ├── orders.php         # Order management
│   └── order_actions.php  # API for order updates
├── css/
│   └── style.css          # All styling
├── database/
│   ├── database.sql       # Database schema & sample data
│   └── setup.php          # Automatic database setup
├── includes/
│   ├── config.php         # Database connection
│   ├── header.php         # Reusable header
│   └── footer.php         # Reusable footer
├── js/
│   └── main.js           # JavaScript interactions
├── index.php             # Landing page
├── login.php             # User login
├── admin_login.php       # Admin login
├── signup.php            # User registration
├── logout.php            # Session destroy
├── customer_dashboard.php # Customer dashboard
├── cart_actions.php      # Shopping cart logic
└── checkout.php          # Checkout process
```

## Database Schema

### Tables

**users**
- id (Primary Key)
- username (Unique)
- email (Unique)
- password (Hashed)
- full_name
- created_at

**pastries**
- id (Primary Key)
- name
- description
- price
- image_url
- category
- is_featured (Boolean)
- created_at

**orders**
- id (Primary Key)
- user_id (Foreign Key)
- total_amount
- status
- order_date

**order_items**
- id (Primary Key)
- order_id (Foreign Key)
- pastry_id (Foreign Key)
- quantity
- price

## Technologies Used

- **Backend:** PHP 7.4+, MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Fonts:** Google Fonts (Playfair Display, Quattrocento)
- **Images:** Unsplash (placeholder images)

## Security Features

- Password hashing using `password_hash()` and `password_verify()`
- SQL injection prevention with `mysqli_real_escape_string()`
- Session-based authentication
- XSS protection with `htmlspecialchars()`
- Form validation (client and server-side)

## Customization

### Change Colors
Edit CSS variables in `css/style.css`:
```css
:root {
    --cream: #FFF8F0;
    --warm-brown: #8B6F47;
    --raspberry: #C84B71;
    --honey: #E8A446;
}
```

### Add More Sample Pastries
Add INSERT statements to `setup.sql` or use the admin panel!

### Modify Layout
All pages use the same header/footer from `includes/` folder for consistency.

## Browser Support

- Chrome (recommended)
- Firefox
- Safari
- Edge
- Opera

## Credits

Design inspired by artisan bakeries and patisseries worldwide.
Built with passion for both coding and pastries! 🥐

## License

Free to use for educational and personal projects.

---

**Enjoy your pastry shop! Happy baking! 🧁**
