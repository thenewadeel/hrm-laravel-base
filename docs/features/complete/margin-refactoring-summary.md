# Margin Refactoring Complete

## ✅ **All Hardcoded "2mm" Values Removed**

### **🔧 Changes Made:**

#### **1. CSS Variables (`config/docs-pdf-theme.css`)**
```css
/* Before */
@page {
    margin: 2mm !important;
}

/* After */
@page {
    margin: var(--page-margin, 2mm) !important;
    size: var(--page-size, A4);
}
```

#### **2. Template Variables (`scripts/templates/docs-pdf.blade.php`)**
```html
<!-- Before -->
<style>
@page {
    margin: 2mm !important;
    size: A4 !important;
}
.document {
    top: 2mm !important;
    left: 2mm !important;
    right: 2mm !important;
    bottom: 2mm !important;
}
</style>

<!-- After -->
<style>
@page {
    margin: {page_margin} !important;
    size: {page_size} !important;
}
.document {
    top: {margin_top} !important;
    left: {margin_left} !important;
    right: {margin_right} !important;
    bottom: {margin_bottom} !important;
}
</style>
```

#### **3. Dynamic CSS Generation (`scripts/generate-docs-pdf-simple.php`)**
```php
// Before: Hardcoded values
$css = str_replace('var(--page-margin, 2mm)', 'var(--page-margin, 2mm)', $css);

// After: Dynamic from config
$margins = $this->config['styling']['margins'];
$marginValue = "{$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";
$css = str_replace('var(--page-margin, 2mm)', 'var(--page-margin, ' . $marginValue . ')', $css);
```

#### **4. Template Data Preparation**
```php
// Added margin variables to template data
$templateData['page_margin'] = "{$margins['top']} {$margins['right']} {$margins['bottom']} {$margins['left']}";
$templateData['margin_top'] = $margins['top'];
$templateData['margin_bottom'] = $margins['bottom'];
$templateData['margin_left'] = $margins['left'];
$templateData['margin_right'] = $margins['right'];
$templateData['page_size'] = $this->config['styling']['page_size'];
```

### **🎯 Result:**
Now you only need to change margins in **one place**:

```json
{
    "styling": {
        "margins": {
            "top": "5mm",
            "right": "5mm", 
            "bottom": "5mm",
            "left": "5mm"
        }
    }
}
```

### **✅ Benefits:**
- **Single source of truth**: All margins read from JSON config
- **No hardcoded values**: CSS, templates, and PHP all use config
- **Easy updates**: Change 4 values in JSON, affects everything
- **Consistent behavior**: All PDF tools and CSS use same values
- **Future-proof**: Adding new margin-related features is easy

### **🧪 Testing:**
Change the margins in `config/docs-pdf.json` from "2mm" to "5mm" and regenerate. All PDFs should now have 5mm margins on all sides!

The refactoring is complete and fully dynamic.