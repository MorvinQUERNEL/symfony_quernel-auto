# QUERNEL AUTO - Architecture CSS et JavaScript

## 📁 Structure des fichiers

### CSS
```
public/css/
├── main.css              # Variables CSS, reset, utilitaires
├── navigation.css        # Navigation responsive avec menu burger
├── carousel.css          # Carrousel moderne avec animations
├── home-modern.css       # Page d'accueil moderne
├── app.css              # Fichier principal d'importation
└── README.md            # Documentation
```

### JavaScript
```
public/js/
├── navigation.js         # Gestion de la navigation
├── carousel.js          # Gestion du carrousel
├── app.js              # Application principale
└── README.md           # Documentation
```

## 🎨 Palette de couleurs

La palette de couleurs est définie dans `main.css` avec des variables CSS :

```css
:root {
    --primary-color: #FF6B00;      /* Orange principal */
    --secondary-color: #000000;    /* Noir */
    --text-color: #333333;         /* Gris foncé */
    --light-color: #ffffff;        /* Blanc */
    --gray-color: #f5f5f5;         /* Gris clair */
    
    /* Couleurs supplémentaires */
    --primary-dark: #e55a00;
    --primary-light: #ff8533;
    --gray-dark: #666666;
    --gray-light: #f8f9fa;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --error-color: #dc3545;
    --info-color: #17a2b8;
}
```

## 📱 Approche Mobile First

L'architecture suit une approche **Mobile First** avec deux breakpoints :

- **Tablettes** : `min-width: 768px`
- **Desktop** : `min-width: 1024px`

## 🚀 Fonctionnalités principales

### Navigation
- Menu burger responsive
- Animations fluides
- Support tactile (swipe)
- Navigation au clavier
- Dropdown menus
- Effets de scroll

### Carrousel
- Autoplay avec pause au hover
- Navigation tactile et souris
- Animations fluides
- Responsive (1-3 slides selon l'écran)
- Indicateurs (dots)
- Barre de progression

### Page d'accueil
- Hero section avec parallax
- Sections services avec animations
- Processus d'achat en 4 étapes
- Formulaire d'inscription moderne
- Effets visuels impressionnants

## 🛠️ Utilisation

### 1. Inclusion des fichiers

Dans votre template Twig principal :

```html
<!-- CSS -->
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<!-- JavaScript -->
<script type="module" src="{{ asset('js/app.js') }}"></script>
```

### 2. Structure HTML pour la navigation

```html
<nav class="navbar">
    <div class="navbar-container">
        <a href="/" class="navbar-brand">
            <img src="/images/logo.png" alt="Quernel Auto" class="navbar-logo">
            Quernel Auto
        </a>
        
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="/" class="nav-link">Accueil</a>
            </li>
            <li class="nav-item">
                <a href="/vehicules" class="nav-link">Véhicules</a>
            </li>
            <!-- ... autres liens -->
        </ul>
        
        <div class="navbar-actions">
            <a href="/login" class="btn-login">Connexion</a>
            <a href="/register" class="btn-register">Inscription</a>
        </div>
        
        <button class="navbar-toggler">
            <span class="navbar-toggler-line"></span>
            <span class="navbar-toggler-line"></span>
            <span class="navbar-toggler-line"></span>
        </button>
    </div>
</nav>
```

### 3. Structure HTML pour le carrousel

```html
<section class="carousel-section">
    <div class="carousel-container">
        <div class="carousel-header">
            <h2 class="carousel-title">Nos véhicules vedettes</h2>
            <p class="carousel-subtitle">Découvrez notre sélection de véhicules d'exception</p>
        </div>
        
        <div class="carousel-track">
            <div class="carousel-item">
                <div class="carousel-image-container">
                    <img src="/images/vehicule1.jpg" alt="Véhicule 1" class="carousel-image">
                    <div class="carousel-image-overlay"></div>
                </div>
                <div class="carousel-content">
                    <h3 class="carousel-vehicle-title">Audi RS7</h3>
                    <p class="carousel-vehicle-subtitle">Sportback Performance</p>
                    <div class="carousel-vehicle-details">
                        <div class="carousel-detail-item">
                            <span class="carousel-detail-icon">📅</span>
                            <span>2023</span>
                        </div>
                        <div class="carousel-detail-item">
                            <span class="carousel-detail-icon">🛣️</span>
                            <span>15 000 km</span>
                        </div>
                    </div>
                    <div class="carousel-price">125 000 €</div>
                    <div class="carousel-actions">
                        <a href="/vehicule/1" class="carousel-btn-view">Voir détails</a>
                        <a href="/vehicule/1/acheter" class="carousel-btn-buy">Acheter</a>
                    </div>
                </div>
            </div>
            <!-- ... autres items -->
        </div>
        
        <button class="carousel-nav carousel-nav-prev" aria-label="Précédent">
            <svg class="carousel-nav-icon" viewBox="0 0 24 24">
                <path d="M15.41 7.41L14 6l-6 6 6 6 1.41-1.41L10.83 12z"/>
            </svg>
        </button>
        <button class="carousel-nav carousel-nav-next" aria-label="Suivant">
            <svg class="carousel-nav-icon" viewBox="0 0 24 24">
                <path d="M10 6L8.59 7.41 13.17 12l-4.58 4.59L10 18l6-6z"/>
            </svg>
        </button>
        
        <div class="carousel-indicators">
            <button class="carousel-dot active" aria-label="Slide 1"></button>
            <button class="carousel-dot" aria-label="Slide 2"></button>
            <button class="carousel-dot" aria-label="Slide 3"></button>
        </div>
        
        <div class="carousel-progress">
            <div class="carousel-progress-bar"></div>
        </div>
    </div>
</section>
```

## 🎯 Classes utilitaires

### Layout
- `.container` - Container centré avec max-width
- `.container-fluid` - Container pleine largeur
- `.section` - Section avec padding vertical
- `.d-flex`, `.d-grid`, `.d-none` - Display utilities

### Espacements
- `.m-0` à `.m-5` - Marges
- `.p-0` à `.p-5` - Paddings
- `.gap-0` à `.gap-5` - Gaps pour grid/flex

### Couleurs
- `.text-primary`, `.text-success`, etc.
- `.bg-primary`, `.bg-light`, etc.

### Animations
- `.animate-fade-in`
- `.animate-slide-left`
- `.animate-slide-right`
- `.animate-scale-in`

## ♿ Accessibilité

- Support complet du clavier
- Attributs ARIA appropriés
- Focus visible
- Skip links
- Support des lecteurs d'écran
- Respect des préférences de réduction de mouvement

## 📱 Responsive

### Mobile (< 768px)
- Navigation en menu burger
- Carrousel : 1 slide visible
- Grilles : 1 colonne
- Boutons empilés verticalement

### Tablette (768px - 1023px)
- Navigation horizontale
- Carrousel : 2 slides visibles
- Grilles : 2 colonnes
- Boutons côte à côte

### Desktop (≥ 1024px)
- Navigation complète
- Carrousel : 3 slides visibles
- Grilles : 3-4 colonnes
- Effets visuels avancés

## 🔧 Personnalisation

### Modifier les couleurs
Éditez les variables CSS dans `main.css` :

```css
:root {
    --primary-color: #VotreCouleur;
    --secondary-color: #VotreCouleur;
    /* ... */
}
```

### Modifier les breakpoints
Ajustez les media queries dans chaque fichier CSS :

```css
@media (min-width: 768px) {
    /* Styles tablettes */
}

@media (min-width: 1024px) {
    /* Styles desktop */
}
```

### Ajouter des animations
Créez vos propres animations dans `main.css` :

```css
@keyframes votreAnimation {
    from { /* état initial */ }
    to { /* état final */ }
}

.votre-classe {
    animation: votreAnimation 0.6s ease-out;
}
```

## 🚀 Performance

- CSS optimisé avec variables
- JavaScript modulaire
- Lazy loading des images
- Debounce/throttle pour les événements
- Intersection Observer pour les animations
- Support des préférences de réduction de mouvement

## 📝 Maintenance

### Ajouter un nouveau composant
1. Créez le fichier CSS dans `public/css/`
2. Créez le fichier JS dans `public/js/`
3. Importez dans `app.css` et `app.js`
4. Documentez dans ce README

### Modifier un composant existant
1. Éditez le fichier CSS/JS correspondant
2. Testez sur tous les breakpoints
3. Vérifiez l'accessibilité
4. Mettez à jour la documentation

## 🤝 Contribution

1. Respectez l'approche Mobile First
2. Utilisez les variables CSS existantes
3. Testez sur tous les appareils
4. Vérifiez l'accessibilité
5. Documentez vos modifications

---

**Version** : 2.0.0  
**Dernière mise à jour** : Décembre 2024  
**Auteur** : Quernel Auto Team 