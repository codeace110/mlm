# MLM Binary Balancer System

This document describes the implementation of a reliable binary balancer system for MLM (Multi-Level Marketing) applications. The system handles direct referral bonuses and downline quota rewards with proper volume propagation and carryover mechanics.

## Overview

The system implements two main types of bonuses:

1. **Direct Referral Bonus** (₱100 each) - Awarded when personal directs form pairs
2. **Downline Quota Bonus** (₱100 each) - Awarded when downline volumes reach level-specific quotas

## Key Features

- ✅ Reliable volume propagation up the upline
- ✅ No double-payment protection
- ✅ Carryover unused volume to future levels
- ✅ Product rewards every 5th bonus
- ✅ Database transactions and row-level locking for concurrency
- ✅ Comprehensive unit tests

## Database Schema

### binary_trees table
```sql
- user_id (FK to users)
- total_left_volume (int, default 0)
- total_right_volume (int, default 0)
- left_consumed (int, default 0)
- right_consumed (int, default 0)
- level_index (int, default 1)
- reward_count (int, default 0)
- direct_pairs_paid (int, default 0)
```

### bonuses table
```sql
- user_id (FK to users)
- amount (decimal 10,2)
- is_product (boolean)
- reward_type (enum: 'direct', 'level')
- level_index (int nullable)
- pair_count (int, default 1)
- description (string)
- status (string, default 'pending')
```

## Core Algorithm

### Volume Propagation
When a new user is placed:
1. Increment sponsor's volume on the placement side
2. Propagate volume up the entire upline
3. Update `total_left_volume` or `total_right_volume` accordingly

### Direct Bonus Calculation
```
pairs_available = min(left_directs_count, right_directs_count)
new_pairs = pairs_available - direct_pairs_paid
for each new_pair:
    issue_reward('direct')
    direct_pairs_paid += 1
```

### Level Processing
For each level:
```
quota = 2^level_index
effective_left = total_left_volume - left_consumed
effective_right = total_right_volume - right_consumed

if effective_left >= quota OR effective_right >= quota:
    issue_reward('level', level_index)

    if both sides >= quota:
        left_consumed += quota
        right_consumed += quota
    else if left >= quota:
        left_consumed += quota
    else:
        right_consumed += quota

    level_index += 1
    continue to next level
```

### Product Reward Logic
```
reward_count += 1
if reward_count % 5 == 0:
    amount = 0, is_product = true
else:
    amount = 100, is_product = false
```

## API Endpoints

### Place User
```http
POST /api/binary/place-user
Content-Type: application/json

{
    "new_user_id": "AKEN123456",
    "sponsor_id": "AKEN789012",
    "preferred_side": "left" // optional: "left" or "right"
}
```

### Get Tree Info
```http
GET /api/binary/tree-info?user_id=AKEN123456
```

### Process Levels
```http
POST /api/binary/process-levels
Content-Type: application/json

{
    "user_id": "AKEN123456"
}
```

## Usage Examples

### Example 1: Direct Pair Bonus
```json
// User has 3 left directs and 3 right directs
{
    "input": {
        "direct_left_count": 3,
        "direct_right_count": 3,
        "direct_pairs_paid": 0
    },
    "output": {
        "bonuses_created": 3,
        "reward_count": 3,
        "direct_pairs_paid": 3,
        "bonuses": [
            {"amount": 100, "is_product": false, "type": "direct"},
            {"amount": 100, "is_product": false, "type": "direct"},
            {"amount": 100, "is_product": false, "type": "direct"}
        ]
    }
}
```

### Example 2: One-Side Quota Achievement
```json
{
    "input": {
        "total_left_volume": 0,
        "total_right_volume": 8,
        "left_consumed": 0,
        "right_consumed": 0,
        "level_index": 3
    },
    "output": {
        "level_completed": 3,
        "right_consumed": 8,
        "level_index": 4,
        "bonus": {"amount": 100, "is_product": false, "type": "level", "level_index": 3}
    }
}
```

### Example 3: Massive One-Sided Growth
```json
{
    "input": {
        "total_left_volume": 30,
        "total_right_volume": 10,
        "level_index": 1
    },
    "processing": [
        "Level 1: quota=2, left=30>=2 → consume 2, level=2",
        "Level 2: quota=4, left=28>=4 → consume 4, level=3",
        "Level 3: quota=8, left=24>=8 → consume 8, level=4",
        "Level 4: quota=16, left=16>=16 → consume 16, level=5"
    ],
    "output": {
        "left_consumed": 30,
        "level_index": 5,
        "bonuses_created": 4,
        "carryover_left": 0,
        "carryover_right": 10
    }
}
```

### Example 4: Simultaneous Both Sides
```json
{
    "input": {
        "total_left_volume": 8,
        "total_right_volume": 8,
        "level_index": 3
    },
    "output": {
        "left_consumed": 8,
        "right_consumed": 8,
        "level_index": 4,
        "bonuses_created": 1, // Only one reward despite both sides meeting quota
        "bonus": {"amount": 100, "is_product": false, "type": "level", "level_index": 3}
    }
}
```

### Example 5: Product Every 5th Reward
```json
{
    "input": {
        "rewards_sequence": [1, 2, 3, 4, 5]
    },
    "output": {
        "bonuses": [
            {"reward": 1, "amount": 100, "is_product": false},
            {"reward": 2, "amount": 100, "is_product": false},
            {"reward": 3, "amount": 100, "is_product": false},
            {"reward": 4, "amount": 100, "is_product": false},
            {"reward": 5, "amount": 0, "is_product": true}
        ]
    }
}
```

## Testing

Run the unit tests:
```bash
php artisan test tests/Unit/BinaryBalancerServiceTest.php
```

Test scenarios cover:
- Direct pair bonus calculation
- One-side quota achievement
- Massive one-sided growth with multiple level completions
- Simultaneous both sides reaching quota
- Product reward every 5th bonus

## Implementation Notes

- All critical operations use database transactions
- Row-level locking prevents race conditions
- Volume propagation is atomic per user placement
- Level processing handles multiple completions in one operation
- Product rewards are based on global reward count per user

## Files Created/Modified

- `app/Services/BinaryBalancerService.php` - Core service implementation
- `app/Models/BinaryTree.php` - Updated with integer casts
- `app/Models/Bonus.php` - Bonus model
- `app/Http/Controllers/BinaryController.php` - API endpoints
- `database/migrations/2025_09_14_080448_add_columns_to_binary_trees_table.php` - Schema updates
- `database/migrations/2025_09_14_080522_create_bonuses_table.php` - Bonuses table
- `database/factories/BinaryTreeFactory.php` - Test factory
- `tests/Unit/BinaryBalancerServiceTest.php` - Comprehensive unit tests
- `routes/api.php` - API routes
- `app/Services/NotificationService.php` - Updated for Bonus objects