# 🎨 PRODIGI - Dark Neon Theme Documentation

## Theme Overview

The PRODIGI marketplace has been redesigned with a **Dark Minimalist Cyber-Tech** theme featuring:

- **Dark Background:** Pure black (#0A0A0A) for maximum contrast
- **Neon Green Accents:** Vibrant green (#39FF14) for CTAs and highlights
- **Minimal Layout:** Generous spacing, clean lines, no clutter
- **Glow Effects:** Subtle neon glows on hover for cyber-tech vibe

---

## 🎨 Color Palette

### Primary Colors
```css
--neon-green: #39FF14        /* Primary action color */
--neon-green-light: #00FF7F  /* Hover states */
--bg-primary: #0A0A0A        /* Main background */
--bg-secondary: #1A1A1A      /* Alternate sections */
--bg-card: #2C2C2C           /* Card backgrounds */
```

### Text Colors
```css
--text-primary: #E5E5E5      /* Primary text */
--text-secondary: #B0B0B0    /* Secondary text */
--text-muted: #808080        /* Muted text */
```

### Borders & Effects
```css
--border-color: #3A3A3A      /* Subtle borders */
--glow-sm: 0 0 10px rgba(57, 255, 20, 0.5)
--glow-md: 0 0 20px rgba(57, 255, 20, 0.5)
--glow-lg: 0 0 30px rgba(57, 255, 20, 0.5)
```

---

## 🚀 Key Design Elements

### 1. **Buttons**

**Primary Button** (Neon Green Border):
```html
<button class="btn btn-primary">Click Me</button>
```
- Transparent background
- Neon green border
- Glows on hover
- Fills with green on hover

**Secondary Button** (Filled):
```html
<button class="btn btn-secondary">Submit</button>
```
- Solid neon green background
- Black text
- Larger glow on hover
- Main CTA button

**Ghost Button** (Subtle):
```html
<button class="btn btn-ghost">View All</button>
```
- Minimal background tint
- Green border
- Subtle hover effect

**Button Sizes:**
```html
<button class="btn btn-sm">Small</button>
<button class="btn">Default</button>
<button class="btn btn-lg">Large</button>
```

---

### 2. **Cards**

**Product/Category Cards:**
- Dark gray background (#2C2C2C)
- 1px subtle border
- Neon green border on hover
- Glow effect on hover
- Lifts up 8px on hover

```html
<div class="card">
    <img class="card-img" src="..." alt="...">
    <div class="card-body">
        <h3 class="card-title">Title</h3>
        <p class="card-text">Description</p>
    </div>
    <div class="card-footer">
        <span class="card-price">₹999</span>
    </div>
</div>
```

---

### 3. **Hero Section**

**Features:**
- Centered content
- Radial gradient background effect (animated pulse)
- Large bold headline with neon highlight
- Spacious layout
- Prominent CTA buttons

```html
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Your Title <span class="highlight">Highlighted</span></h1>
            <p>Subtitle text here</p>
            <div class="hero-buttons">
                <a href="#" class="btn btn-secondary btn-lg">Primary Action</a>
                <a href="#" class="btn btn-primary btn-lg">Secondary Action</a>
            </div>
        </div>
    </div>
</section>
```

---

### 4. **Navigation**

**Header:**
- Sticky at top
- Dark background with blur
- Logo: "PRO" in neon green, "DIGI" in white
- Links change color on hover with glow
- Neon bordered buttons

```html
<header>
    <nav class="navbar container">
        <a href="/" class="logo">PRO<span>DIGI</span></a>
        <ul class="nav-links">
            <li><a href="#">Home</a></li>
            <li><a href="#">Products</a></li>
        </ul>
        <div class="nav-links">
            <a href="#" class="btn btn-outline btn-sm">Login</a>
            <a href="#" class="btn btn-secondary btn-sm">Sign Up</a>
        </div>
    </nav>
</header>
```

---

### 5. **Forms**

**Input Fields:**
- Dark background (#1A1A1A)
- Subtle border
- Neon green glow on focus
- Light gray text

```html
<div class="form-group">
    <label class="form-label">Email</label>
    <input type="email" class="form-control" placeholder="your@email.com">
</div>
```

---

### 6. **Typography**

**Headings:**
```html
<h1 class="section-title">Main Title</h1>
<p class="section-subtitle">Subtitle description</p>
```

**Features:**
- Section title has neon green underline
- Increased letter-spacing for modern look
- Light font weight (300) for body text
- Bold (600-700) for headings

---

### 7. **Grid Layouts**

**Products Grid:**
```html
<div class="products-grid">
    <!-- 4 columns on desktop, responsive -->
    <div class="product-card">...</div>
    <div class="product-card">...</div>
</div>
```

**Categories Grid:**
```html
<div class="categories-grid">
    <!-- 3-4 columns, auto-fit -->
    <div class="category-card">...</div>
</div>
```

---

## 📦 Components Library

### Badges
```html
<span class="badge badge-primary">New</span>
<span class="product-badge">Featured</span>
```

### Alerts
```html
<div class="alert alert-success">Success message</div>
<div class="alert alert-danger">Error message</div>
<div class="alert alert-warning">Warning message</div>
```

### Loading Spinner
```html
<div class="loading"></div>
```

---

## 🎭 Hover Effects

### Card Hover:
- Border changes to neon green
- Box shadow adds glow
- Lifts 8px upward
- Smooth transition (0.3s)

### Button Hover:
- Primary: Fills with neon green
- Secondary: Intensifies glow
- Outline: Border changes to green

### Link Hover:
- Text color changes to neon green
- Adds subtle text shadow (glow)
- Smooth color transition

---

## 📱 Responsive Breakpoints

```css
/* Desktop: Default */
/* Tablet: max-width: 768px */
/* Mobile: max-width: 480px */
```

**Mobile Changes:**
- Single column layouts
- Larger touch targets
- Full-width buttons
- Reduced spacing
- Hidden navigation (hamburger menu)

---

## 🔧 Usage Examples

### Complete Page Structure:
```html
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="assets/css/dark-neon-theme.css">
</head>
<body>
    <header><!-- Navigation --></header>
    
    <section class="hero">
        <!-- Hero content -->
    </section>
    
    <section>
        <div class="container">
            <h2 class="section-title">Section Title</h2>
            <p class="section-subtitle">Description</p>
            
            <div class="products-grid">
                <!-- Cards -->
            </div>
        </div>
    </section>
    
    <footer><!-- Footer --></footer>
</body>
</html>
```

---

## 🎨 Customization

### Changing Neon Color:
Edit these variables in `dark-neon-theme.css`:
```css
:root {
    --neon-green: #YOUR_COLOR;
    --neon-green-light: #YOUR_LIGHTER_COLOR;
}
```

### Popular Alternatives:
- **Cyan:** #00FFFF
- **Pink:** #FF006E
- **Blue:** #0080FF
- **Purple:** #B537FF
- **Orange:** #FF6B35

---

## ✨ Best Practices

1. **Use neon green sparingly** - Only for CTAs and important elements
2. **Maintain high contrast** - Dark backgrounds + light text
3. **Add generous spacing** - Don't crowd elements
4. **Consistent glow effects** - Use predefined glow variables
5. **Test on dark mode devices** - Ensure visibility

---

## 📂 File Structure

```
assets/
├── css/
│   ├── dark-neon-theme.css     ← Main theme file
│   ├── auth-dark.css            ← Auth pages styling
│   └── style.css                ← Old theme (backup)
```

---

## 🚀 Applying to New Pages

1. Link the CSS file:
```html
<link rel="stylesheet" href="assets/css/dark-neon-theme.css">
```

2. Use standard components:
```html
<section>
    <div class="container">
        <h2 class="section-title">Title</h2>
        <div class="grid-3">
            <div class="card">...</div>
        </div>
    </div>
</section>
```

3. Follow the spacing system:
- Use `--spacing-xs` to `--spacing-xl` variables
- Use margin utilities: `mt-1`, `mb-2`, `p-3`

---

## 🎯 Theme Philosophy

**Minimalism:**
- Remove unnecessary elements
- Focus user attention with color
- Lots of breathing room

**Cyber-Tech Aesthetic:**
- Dark backgrounds (night mode friendly)
- Neon accents (futuristic feel)
- Subtle glows (sci-fi vibe)
- Clean typography (modern)

**User Experience:**
- High contrast for readability
- Clear visual hierarchy
- Obvious interactive elements
- Smooth, satisfying animations

---

## 📊 Performance Notes

- **Lightweight:** Single CSS file, no frameworks
- **Fast rendering:** CSS variables for dynamic theming
- **Optimized animations:** Hardware-accelerated transforms
- **Minimal shadows:** Subtle glows don't impact performance

---

## 🔄 Migration from Old Theme

### Quick Replace:
```html
<!-- Old -->
<link rel="stylesheet" href="assets/css/style.css">

<!-- New -->
<link rel="stylesheet" href="assets/css/dark-neon-theme.css">
```

### Class Changes:
- `.gradient-text` → `.highlight` or `.text-neon`
- `.btn-outline` → Updated with neon borders
- `.hero-title` → Standard `<h1>` in `.hero`
- Card structures remain compatible

---

**Last Updated:** October 19, 2025  
**Version:** 2.0.0 (Dark Neon Edition)  
**Status:** ✅ Production Ready

**Enjoy the cyber marketplace experience! 🚀✨**
