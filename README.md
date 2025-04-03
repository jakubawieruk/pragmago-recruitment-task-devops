# Task Manager API - DevOps Recruitment Task

## Recruitment Task

Your task is:

1. **Application Containerization**

2. **CI/CD Configuration**
   - Choose one of the CI systems (Jenkins or GitHub Actions)
   - Configure a pipeline that will:
     - Run static code analysis
     - Build and tag Docker images
  - Use RoadRunner instead of traditional PHP-FPM + web server setup

## Tips

1. Familiarize yourself with the application code before starting work
2. Remember to apply security best practices
3. Document your solutions
4. Be prepared to discuss your technical decisions during the interview

## Migrations

```bash
# Database configuration in .env
# DATABASE_URL="postgresql://username:password@localhost:5432/task_manager"

# Running migrations
php bin/console doctrine:migrations:migrate
```

## Project Description

Task Manager API is a simple application written in Symfony 6.3, used for managing tasks (todo list) via REST API. The application includes the following functionalities:

- Displaying a list of tasks
- Adding new tasks
- Editing existing tasks
- Deleting tasks
- Marking tasks as completed
- Task prioritization

The application uses PostgreSQL database for storing tasks and Redis for caching query results.

## Tests and code quality

The project includes:

1. Unit and functional tests using PHPUnit
```bash
composer test
```
2. Static code analysis using PHPStan (level 5)
```bash
composer stan
```
3. Code formatting using Symplify/Easy-Coding-Standard
```bash
# To check
composer cs

# To fix
composer cs-fix
```

## Technical Requirements

- PHP 8.1 or higher
- Composer
- PostgreSQL 15
- Redis 6.0
- Symfony CLI (optional)