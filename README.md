# Sboj.cz

A simple PHP/MySQL job portal web application for browsing and managing job offers, with user registration, login, profile pages, and job-application workflows.

## About the project

- Built a self-contained PHP job board prototype with user registration and login.
- Created profile management and image upload support for users and job postings.
- Implemented job listing, search, pagination, and application flows.
- Added session-based authentication and access control for posting owners.
- Included a responsive HTML/CSS layout with lightweight JavaScript validation.

## Features

- User registration and login with session management
- Job listing and search with pagination
- Job detail view with apply, edit, and delete actions
- User profile editing and avatar upload
- Role-based flows for job seekers and posting owners
- Server-rendered PHP templates with shared database helper functions

## Technologies

- PHP
- MySQL / PDO
- HTML
- CSS
- JavaScript

## Running locally

1. Place the project in a local PHP web server root (for example, Apache or Nginx with PHP enabled).
2. Ensure PHP has PDO for MySQL and the GD extension enabled.
3. Copy `.env.example` to `.env` and update the database credentials to match your local environment.
4. Create the required tables for users, job postings, and applications. The repository does not include a database schema file.
5. Open the site in a browser through the project root, typically via `index.php`.

## Project context

Created as a standalone PHP/MySQL web application prototype for browsing and managing job offers, with a focus on practical full-stack learning rather than a production-ready release.
