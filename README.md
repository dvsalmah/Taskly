<div align="center">
  <img src="assets/taskly-HD.png" alt="Taskly Logo" width="180" />
  <p><strong>A clean, modern task management web application built with PHP & MySQL.</strong></p>
  <p>
    <img src="https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white" />
    <img src="https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white" />
    <img src="https://img.shields.io/badge/JavaScript-ES6-F7DF1E?style=flat-square&logo=javascript&logoColor=black" />
  </p>
</div>

## Overview

**Taskly** is a task management platform that helps users organize, prioritize, and track their work — all in one place. It features a real-time deadline tracking, smart vital task detection, and customizable task categories.

## Features
### Homepage
- Real-time overview of task statistics (total, completed, in-progress, not started).
- Progress bar showing completion percentage.
- List of upcoming to-do items and recently completed tasks.
- Vital task highlights shown with a flame badge.

### My Task
- Create, view, edit, and delete tasks with full detail forms.
- Each task supports: title, description, category, priority (Low / Medium / High), status, and deadline.
- Live Deadline Badges (no page refresh needed).
- Filters: Filter by status chip (All / Not Started / In Progress / Completed) and priority dropdown simultaneously.
- Search — Live search across task title, description, and category name.

### Vital Task
- Automatically surfaces tasks that are critical: **High priority** and/or **deadline within 48 hours**.
- Completed tasks are excluded regardless of priority.
- Inline status update without leaving the page.

### Task Categories
- Create custom categories with a name and a color (choose from presets).
- Each category shows how many tasks are assigned to it.
- Deleting a category detaches it from tasks without deleting the tasks themselves.

### Profile
- Update first name, last name, email, contact, and position.
- Upload a profile photo (JPG/PNG, max 2 MB); old photo is deleted automatically.
- Changes are saved to the database and reflected in the navbar immediately.

### Help Center
- Visual guide to all major features with icons.
- FAQ accordion with common questions and answers.

## Usage

| Step | Action |
|------|--------|
| 1 | Visit the landing page and click **Get Started** to register. |
| 2 | Fill in your name, username, email, and password. |
| 3 | Log in with your credentials. Optionally check **Keep me signed in**. |
| 4 | From the **Homepage**, see an overview of your task progress. |
| 5 | Go to **Task Categories** first and create categories (e.g. Work, Personal). |
| 6 | Go to **My Task** and click **+ Add Task** to create your first task. |
| 7 | Click any task card to preview it. From the preview you can edit, delete, or update its status. |
| 8 | Check **Vital Task** to see tasks that need immediate attention. |
| 9 | Click your avatar in the navbar to open **Profile** and update your info or photo. |
| 10 | Click **Logout** in the sidebar to sign out securely. |

---