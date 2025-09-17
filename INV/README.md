# Inventory Management System

A modern, responsive web-based inventory management system built with HTML, CSS, and JavaScript. This system provides comprehensive inventory tracking, POS functionality, reservation management, supplier management, and reporting capabilities.

## Features

### 🏠 Dashboard
- **Overview Cards**: Total products, low stock items, today's sales, and active reservations
- **Recent Activity**: Real-time activity feed showing all system actions
- **Responsive Design**: Works seamlessly on desktop, tablet, and mobile devices

### 📦 Inventory Management
- **Product Management**: Add, edit, and delete products
- **Category Filtering**: Filter products by category (Electronics, Clothing, Books, Food)
- **Search Functionality**: Quick search through product names
- **Stock Tracking**: Automatic status updates for low stock items
- **Status Indicators**: Visual badges showing stock status

### 💰 Point of Sale (POS)
- **Product Catalog**: Visual product cards with pricing and stock information
- **Shopping Cart**: Add/remove items with quantity controls
- **Real-time Total**: Automatic calculation of cart total
- **Stock Validation**: Prevents overselling with real-time stock checks
- **Transaction Processing**: Complete sales with automatic inventory updates

### 📅 Reservation Management
- **Customer Reservations**: Create and manage customer reservations
- **Status Tracking**: Track reservation status (Pending, Confirmed, Completed, Cancelled)
- **Date Management**: Set reservation dates with validation
- **Product Integration**: Link reservations to available inventory items
- **Search & Filter**: Find reservations by customer, product, or status

### 🚚 Supplier Management
- **Supplier Database**: Maintain comprehensive supplier information
- **Contact Details**: Store supplier names, contacts, emails, and phone numbers
- **Status Tracking**: Active/inactive supplier status
- **Search Functionality**: Quick search through supplier information
- **CRUD Operations**: Full create, read, update, delete functionality

### 📊 Reports & Analytics
- **Sales Overview**: Visual representation of sales data
- **Top Products**: List of best-performing products
- **Export Functionality**: Generate and download reports
- **Multiple Report Types**: Sales, inventory, and reservation reports

### 🔐 User Management
- **Manager Interface**: Designed for business managers
- **Logout Functionality**: Secure logout with confirmation
- **Session Management**: Local storage for data persistence

## Getting Started

### Prerequisites
- Modern web browser (Chrome, Firefox, Safari, Edge)
- No additional software installation required

### Installation
1. Download or clone the project files
2. Open `index.html` in your web browser
3. The system will load with sample data for demonstration

### File Structure
```
INVENTORY/
├── index.html          # Main application file
├── styles.css          # CSS styling and responsive design
├── script.js           # JavaScript functionality
└── README.md           # This documentation file
```

## Usage Guide

### Dashboard
- View key metrics and recent activities
- Navigate between different sections using the sidebar

### Adding Products
1. Navigate to "Inventory Management"
2. Click "Add Product" button
3. Fill in product details (name, category, quantity, price)
4. Click "Add Product" to save

### Processing Sales (POS)
1. Navigate to "POS" section
2. Search for products or browse the catalog
3. Click on products to add to cart
4. Adjust quantities using +/- buttons
5. Click "Process Payment" to complete the sale

### Creating Reservations
1. Navigate to "Reservation Management"
2. Click "New Reservation"
3. Enter customer details and select product
4. Set reservation date and quantity
5. Click "Create Reservation"

### Managing Suppliers
1. Navigate to "Supplier Management"
2. Click "Add Supplier" to add new suppliers
3. Use search to find specific suppliers
4. Edit or delete suppliers as needed

### Generating Reports
1. Navigate to "Reports & Analytics"
2. Select report type from dropdown
3. Click "Export" to generate report

## Technical Features

### Responsive Design
- Mobile-first approach
- Collapsible sidebar for mobile devices
- Touch-friendly interface elements
- Optimized for all screen sizes

### Data Persistence
- Local storage for data persistence
- Automatic data saving
- Sample data included for demonstration

### Modern UI/UX
- Clean, professional design
- Smooth animations and transitions
- Intuitive navigation
- Color-coded status indicators

### Search & Filter
- Real-time search functionality
- Multiple filter options
- Dynamic table updates
- Efficient data handling

## Browser Compatibility

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ❌ Internet Explorer (not supported)

## Customization

### Adding New Categories
Edit the category options in both HTML and JavaScript files:
- `index.html`: Update select options in forms
- `script.js`: Update category arrays in filter functions

### Modifying Styling
- Edit `styles.css` to customize colors, fonts, and layout
- CSS variables can be added for easy theme customization
- Responsive breakpoints can be adjusted

### Extending Functionality
- Add new sections by following the existing pattern
- Implement additional features in `script.js`
- Add new modal forms for data entry

## Data Management

### Sample Data
The system includes sample data for:
- 5 sample products across different categories
- 3 sample suppliers with contact information
- 2 sample reservations

### Data Storage
- All data is stored in browser's localStorage
- Data persists between browser sessions
- No server required for basic functionality

### Data Export
- Reports can be generated and exported
- Data can be backed up by accessing browser's developer tools
- JSON format for easy data migration

## Security Considerations

### Client-Side Security
- Input validation on all forms
- XSS prevention through proper data handling
- Confirmation dialogs for destructive actions

### Data Protection
- Local storage encryption (can be implemented)
- Session management
- Secure logout functionality

## Future Enhancements

### Potential Additions
- User authentication and role-based access
- Database integration (MySQL, PostgreSQL)
- Real-time notifications
- Barcode scanning integration
- Email notifications
- Advanced reporting with charts
- Multi-language support
- Backup and restore functionality

### API Integration
- RESTful API for backend integration
- Real-time data synchronization
- Cloud storage integration
- Third-party service integration

## Support

For questions or issues:
1. Check browser console for error messages
2. Ensure all files are in the same directory
3. Verify browser compatibility
4. Clear browser cache if experiencing issues

## License

This project is open source and available under the MIT License.

---

**Note**: This is a demonstration system. For production use, consider implementing proper security measures, database integration, and user authentication.
