# Binary Network Tree Visualization

## Overview

The Binary Network Tree Visualization is a core feature of the AKEN MLM system that provides users with an interactive, visual representation of their binary compensation network. This feature allows users to view their downline structure, track volumes, carryover values, and navigate through different levels of their network hierarchy.

## Architecture

### Backend (PHP/Laravel)

#### Controller: `DashboardController.php`

The network visualization is powered by the `network()` method in `DashboardController.php` (lines 50-57), which:

1. Retrieves the authenticated user
2. Calls `buildBinaryTreeForView()` from `BinaryTreeService` to generate the hierarchical data structure
3. Passes the data to the `dashboard-network.blade.php` view

#### Data Generation: `buildBinaryTreeForView()` Method

**Location:** `app/Services/BinaryTreeService.php` (lines 343-408)

**Purpose:** Recursively builds a hierarchical array representing the user's binary network tree.

**Parameters:**
- `$user` (User model): The user whose network tree to build
- `$depth` (int): Current recursion depth (default: 0)
- `$maxDepth` (int): Maximum depth to traverse (default: 10 levels)

**Data Sources:**
- `binary_trees` table: Stores volume and consumption data for each user
- `users` table: Provides user information and sponsor relationships

**Algorithm:**
1. Retrieves binary tree data from database for the current user
2. Calculates effective volumes (total - consumed = carryover)
3. Recursively processes left and right children
4. Returns hierarchical array with user data and children

### Frontend (HTML/JavaScript/Canvas)

#### View: `resources/views/dashboard-network.blade.php`

**Key Components:**
- **Debug Section** (lines 99-103): Displays raw JSON data for development
- **Canvas Element** (line 107): HTML5 canvas for tree rendering
- **Statistics Cards** (lines 64-97): Network metrics display
- **Color Legend** (lines 27-61): Node color coding reference

#### JavaScript Functions

**Main Functions:**
- `drawBinaryTree()` (lines 203-242): Main rendering function
- `drawNode()` (lines 243-358): Recursive node drawing
- `updateNetworkStats()` (lines 179-201): Updates statistics counters

**Interactive Features:**
- **Zoom:** Mouse wheel zoom (0.3x to 4x scale)
- **Pan:** Click and drag to navigate
- **Level Filtering:** Dropdown to limit display depth
- **Dynamic Canvas Sizing:** Adjusts based on selected depth

## Data Structure

### Network Tree Array Structure

```php
[
    'name' => string,           // User's full name
    'id' => int,               // User ID
    'level' => int,            // Tree level (1-based)
    'left_volume' => float,    // Total left leg volume
    'right_volume' => float,   // Total right leg volume
    'carryover_left' => float, // Effective left volume (total - consumed)
    'carryover_right' => float,// Effective right volume (total - consumed)
    'profile_image' => string, // Profile image URL
    'children' => [            // Array of child nodes [left, right]
        0 => array|null,       // Left child node or null
        1 => array|null        // Right child node or null
    ]
]
```

### Node Types and Colors

| Node Type | Color | Description |
|-----------|-------|-------------|
| Root (You) | Blue (#2196F3) | Current authenticated user |
| Direct Referral (No Carryover) | Yellow (#FFD700) | Level 1 with balanced volumes |
| Direct Referral (With Carryover) | Orange-Red (#FF6B35) | Level 1 with volume imbalance |
| Spillover (No Carryover) | Green (#4CAF50) | Level 2+ with balanced volumes |
| Spillover (With Carryover) | Brown (#8B4513) | Level 2+ with volume imbalance |
| Empty Slot | Light Gray (#f0f0f0) | Available position in tree |

## Usage

### Accessing the Feature

1. User logs into the MLM dashboard
2. Navigate to "Network" section
3. View displays the interactive binary tree

### Navigation Controls

- **Level Selector:** Choose depth (1, 2, 3, 5, or 10 levels)
- **Zoom:** Use mouse wheel to zoom in/out
- **Pan:** Click and drag to move around the tree
- **Refresh:** Reload the page to update data

### Reading the Visualization

- **Node Labels:** Show user initials, full name, and volume data
- **Connecting Lines:** Indicate parent-child relationships
- **Colors:** Represent node type and carryover status
- **Empty Nodes:** Gray circles showing available positions

## Technical Details

### Performance Considerations

- **Recursion Limit:** Maximum 10 levels to prevent stack overflow
- **Canvas Sizing:** Dynamic sizing based on tree depth
- **Data Loading:** Single query with recursive processing
- **Memory Usage:** Tree structure loaded entirely in memory

### Database Dependencies

**Required Tables:**
- `users`: User information and sponsor relationships
- `binary_trees`: Volume tracking and consumption data

**Key Fields:**
- `users.sponsor_id`: Parent relationship
- `binary_trees.user_id`: Links to user
- `binary_trees.left_child_id/right_child_id`: Binary positions
- `binary_trees.total_left_volume/total_right_volume`: Volume tracking
- `binary_trees.left_consumed/right_consumed`: Consumption tracking

### JavaScript Dependencies

- **Canvas API:** HTML5 canvas for rendering
- **Event Listeners:** Mouse events for interaction
- **JSON Data:** Server-side data passed to client

## API Reference

### Controller Methods

#### `network()`
```php
public function network()
```
**Returns:** View with network tree data
**Route:** `GET /network`

#### `buildBinaryTree(User $user, int $depth = 0, int $maxDepth = 10)`
**Parameters:**
- `$user`: User model instance
- `$depth`: Current recursion depth
- `$maxDepth`: Maximum tree depth

**Returns:** Hierarchical array or null

### JavaScript Functions

#### `drawBinaryTree()`
Renders the complete binary tree on canvas

#### `drawNode(node, x, y, depth, maxDepth, ctx, ...)`
Recursively draws individual nodes and their children

#### `updateNetworkStats()`
Calculates and updates network statistics counters

## Troubleshooting

### Common Issues

**Canvas Not Rendering:**
- Check browser canvas support
- Verify JavaScript console for errors
- Ensure network data is properly loaded

**Performance Issues:**
- Large networks (>1000 nodes) may cause slowdown
- Consider increasing recursion limit or implementing pagination
- Check database query performance

**Data Inconsistencies:**
- Verify binary_trees table data integrity
- Check user relationships in users table
- Ensure proper indexing on foreign keys

### Debug Information

The debug section in the view displays the raw JSON data structure. Use this to:
- Verify data loading
- Check node relationships
- Debug volume calculations
- Validate tree structure

### Browser Compatibility

- Modern browsers with Canvas API support
- Tested on Chrome, Firefox, Safari, Edge
- Mobile browsers may have limited interaction

## Future Enhancements

Potential improvements for the visualization:
- Node tooltips with detailed information
- Animation transitions between levels
- Export functionality (PNG/PDF)
- Search and highlight specific users
- Real-time updates via WebSocket
- Mobile-responsive design
- Performance optimization for large networks

## Related Files

- `app/Http/Controllers/DashboardController.php`: Backend logic
- `app/Services/BinaryTreeService.php`: Tree building service
- `resources/views/dashboard-network.blade.php`: Frontend view
- `database/migrations/`: Binary tree table migrations
- `routes/web.php`: Route definitions
- `tests/Feature/DashboardNetworkTest.php`: Feature tests