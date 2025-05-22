# Reizoko - Custom Control Panel Styling for ExpressionEngine

Reizoko is a "powerful" ExpressionEngine addon that allows you to customize the appearance and functionality of your ExpressionEngine Control Panel with custom CSS and JavaScript. It features an "advanced" element picker tool that makes targeting specific elements for styling quick and efficient.

## Features

- **Custom CSS & JavaScript**: Add custom styles and scripts to your ExpressionEngine Control Panel
- **Multi-site Support**: Different styles for each site in a multi-site setup
- **External File Support**: Load CSS and JavaScript from external URLs
- **Advanced Element Picker**: Interactive tool to select and generate CSS selectors

## Requirements

- ExpressionEngine 7.5 or higher
- PHP 7.4 or higher

## Installation

1. Download the Reizoko addon files
2. Upload the `reizoko` folder to your `/system/user/addons/` directory
3. Go to the ExpressionEngine Control Panel
4. Navigate to **Add-Ons**
5. Find "Reizoko" in the list and click **Install**

## Usage

### Accessing Settings

1. Go to **Add-Ons** in your ExpressionEngine Control Panel
2. Find "Reizoko" and click **Settings**

### Adding Custom CSS

1. In the Custom CSS section, you can either:
   - **Direct Code**: Write CSS directly in the textarea
   - **External URL**: Link to an external CSS file

2. Use the **Pick Element** button to interactively select elements on your Control Panel for styling

### Adding Custom JavaScript

1. In the Custom JavaScript section, you can either:
   - **Direct Code**: Write JavaScript directly in the textarea
   - **External URL**: Link to an external JavaScript file

### Using the Element Picker

The element picker is a powerful tool that helps you target specific elements in the ExpressionEngine Control Panel:

1. **Activate Picker**: Click the "Pick Element" button next to any CSS textarea
2. **Select Elements**: Move your mouse over elements in the Control Panel and click to select
3. **View Selectors**: Multiple CSS selector options will appear in the selector list
4. **Use Selectors**: Click any selector to copy it to your clipboard and insert it into your CSS
5. **Persistent List**: The selector list remains visible and accumulates selections from multiple picks
6. **Clear List**: Use the "Clear List" button to empty the current selector list

### Element Picker Features

- **Multiple Selector Types**: Get ID, class, attribute, and hierarchical selectors
- **Smart Filtering**: Automatically filters out Reizoko's own CSS classes
- **Duplicate Detection**: Prevents showing the same selector multiple times

## Multi-site Support

If you're running a multi-site ExpressionEngine installation, Reizoko provides separate settings for each site:

- Each site can have its own custom CSS and JavaScript
- Site labels are automatically displayed in the settings interface

## File Structure

```
reizoko/
├── addon.setup.php          # Addon configuration
├── ext.reizoko.php          # Extension hooks for CSS/JS injection
├── mcp.reizoko.php          # Control Panel module with settings interface
├── mod.reizoko.php          # Base module class
├── upd.reizoko.php          # Update/install routines
├── language/
│   └── english/
│       └── reizoko_lang.php # Language strings
└── README.md               # This file
```

## Version History

### 1.3.37
- Enhanced element picker with persistent selector list
- Added accumulation of multiple element selections
- Removed intrusive alert dialog
- Enhanced duplicate detection

### 1.0.3
- Added element picker tool with history functionality
- Implemented localStorage-based selector history
- Added CodeMirror integration for syntax highlighting
- Multi-site support for different CSS/JS per site

### 1.0.0
- Initial release
- Basic custom CSS and JavaScript support
- External file loading capability

## Tips & Best Practices

1. **Use the Element Picker**: Instead of manually writing selectors, use the element picker to ensure accuracy
2. **Test Thoroughly**: Always test your custom styles across different sections of the Control Panel
3. **Backup Settings**: Consider backing up your custom CSS and JavaScript code
4. **External Files**: For large customizations, consider using external files for better organization
5. **Browser Compatibility**: Test your customizations in different browsers used by your team

## Troubleshooting

### Element Picker Not Working
- Ensure JavaScript is enabled in your browser
- Check browser console for any JavaScript errors
- Try refreshing the page and activating the picker again

### Styles Not Applying
- Check for CSS syntax errors
- Verify that your selectors are specific enough
- Use browser developer tools to inspect elements and test selectors

### External Files Not Loading
- Ensure the external URLs are accessible
- Check for HTTPS/HTTP mixed content issues
- Verify CORS headers if loading from different domains

## Support

For issues, questions, or feature requests, please contact:
- **Authors**: Vincent Rijnbeek & Pontus Madsen
- **Website**: [reizoko.jp](https://reizoko.jp)

## License

This addon is provided as-is for ExpressionEngine installations. Please ensure you have appropriate backups before making extensive Control Panel customizations.

---

**Note**: This addon modifies the ExpressionEngine Control Panel interface. Use with caution in production environments and always test changes thoroughly.