# Bumpa Assessment - Loyalty Program API

Hello team,

First, I want to apologize for pushing this in one commit. I actually built it all out in one sitting.
I ideally commit after completing assigned tasks (or feature as the case maybe). But in this case the task was the entire assessment so I built it all in one go. But I have adjusted and pushed more offent to show human progress on the frontend repo.

At first when I read the instructions, I was confused about the whole thing, but after digging deeper used some ai models to understand the requirements better. I got the idea and built the whole thing myself.

## Project Overview

This is a Loyalty Program feature where users unlock achievements and earn badges based on their purchases.
- **Achievements**: Unlocked by reaching purchase milestones.
- **Badges**: Awarded alongside specific achievements.
- **Cashback**: Triggered when a badge is unlocked (simulated).

## Setup Instructions

Here's how to get it running on your local machine.

### Prerequisites
- PHP 8.2+
- Composer
- PostgreSQL (or your preferred DB)

### Installation

1.  **Clone the repo** (if you haven't already)
2.  **Install Dependencies**:
    ```bash
    composer install
    ```
3.  **Environment Setup**:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    *Make sure to update your `.env` file with your database credentials!*

4.  **Database & Migrations**:
    ```bash
    php artisan migrate --seed
    ```

5.  **Serve the Application**:
    ```bash
    php artisan serve
    ```

## API Documentation

Postman documentation link: https://documenter.getpostman.com/view/50292908/2sBXVo8ngJ

### 1. Make a Purchase
This endpoint simulates a user making a purchase. It checks for unlocked achievements and badges automatically.

-   **Endpoint**: `POST /api/users/purchase`
-   **Body**:
    ```json
    {
      "email": "user@example.com",
      "amount": 5000,
    }
    ```

### 2. Get User Achievements
Check what a user has unlocked and their progress.

-   **Endpoint**: `GET /api/users/{email}/achievements`
-   **Response**:
    ```json
    {
      "success": true,
      "message": "Achievements retrieved successfully",
      "data": {
        "unlocked_achievements": ["First Purchase", "Big Spender"],
        "next_available_achievements": ["Loyal Customer"],
        "current_badge": "Bronze",
        "next_badge": "Silver",
        "remaining_to_unlock_next_badge": 2500
      }
    }
    ```
N/B: Check for seeded users in the database.


## How It Works (My Logic)

I built this using a Service-based architecture to keep the Controllers clean.
-   **PurchaseService**: Handles the transaction logic and calls the AchievementService.
-   **AchievementService**: This is the brains. It checks the user's total spend against the achievement requirements. If a milestone is hit, it unlocks the achievement, awards the badge, and fires the `BadgeUnlocked` event.
