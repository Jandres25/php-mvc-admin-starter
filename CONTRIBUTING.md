# Contributing to ProyectoBase

Thank you for considering contributing to ProyectoBase! This document outlines the guidelines and standards for contributing to this PHP MVC authentication starter project.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Setup](#development-setup)
- [Code Standards](#code-standards)
- [Documentation Standards](#documentation-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Version Management](#version-management)

## Getting Started

1. Fork the repository on GitHub
2. Clone your fork locally
3. Set up the development environment following the README instructions
4. Create a new branch for your feature or bugfix

## Development Setup

```bash
# Clone your fork
git clone https://github.com/yourusername/php-mvc-admin-starter.git
cd php-mvc-admin-starter

# Set up upstream remote
git remote add upstream https://github.com/Jandres25/php-mvc-admin-starter.git

# Create and switch to a new branch
git checkout -b feature/your-feature-name
```

## Code Standards

### PHP Code Standards

- Follow PSR-4 autoloading standards
- Use meaningful class and method names
- Include proper error handling
- Maintain consistent indentation (4 spaces)
- Ensure all PHP files have proper PHPDoc documentation

### JavaScript Code Standards

- Use ES6+ features where appropriate
- Follow consistent naming conventions (camelCase)
- Include JSDoc documentation for functions
- Maintain proper code organization in module files

### Database Standards

- Use prepared statements for all database queries
- Follow consistent naming conventions for tables and columns
- Include proper foreign key relationships
- Document schema changes

## Documentation Standards

### PHPDoc Requirements

All PHP classes, methods, and files must include PHPDoc documentation:

```php
/**
 * Brief description of the class/method/file
 * 
 * Detailed description if necessary
 * 
 * @package ProyectoBase
 * @subpackage [Module Name] (e.g., Controllers\Users, Models, Services)
 * @author Jandres25
 * @version 1.0
 * 
 * @param type $parameter Description of parameter (for methods)
 * @return type Description of return value (for methods)
 * @throws ExceptionType Description of when exception is thrown
 */
```

### JSDoc Requirements

JavaScript functions and modules should include JSDoc documentation:

```javascript
/**
 * filename.js - Brief description
 * 
 * Detailed description of the module's purpose
 * 
 * @package ProyectoBase
 * @subpackage JavaScript\[Module]
 * @author Jandres25
 * @version 1.0
 */

/**
 * Brief description of function
 * @param {type} parameter - Description of parameter
 * @returns {type} Description of return value
 */
function myFunction(parameter) {
    // Implementation
}
```

## Commit Guidelines

We follow [Conventional Commits](https://conventionalcommits.org/) for commit messages:

### Format
```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only changes
- `style`: Code style changes (formatting, semicolons, etc)
- `refactor`: Code refactoring without feature changes
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

### Examples
```bash
feat: add user profile image upload functionality
fix: resolve session timeout issue in admin panel
docs: update installation instructions in README
refactor: reorganize user controller methods
```

## Pull Request Process

1. **Update Documentation**: Ensure all new code is properly documented
2. **Update CHANGELOG**: Add your changes to the `[Unreleased]` section
3. **Test Your Changes**: Verify all functionality works as expected
4. **Code Review**: Request review from maintainers
5. **Address Feedback**: Make necessary changes based on review comments

### Pull Request Template

```markdown
## Description
Brief description of what this PR does.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Documentation update
- [ ] Code refactoring

## Testing
- [ ] Tested locally
- [ ] Added/updated tests
- [ ] Documentation updated

## Checklist
- [ ] PHPDoc/JSDoc documentation added
- [ ] CHANGELOG.md updated
- [ ] Code follows project standards
- [ ] No breaking changes (or properly documented)
```

## Version Management

### Semantic Versioning

We follow [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

### Release Process

1. Update version numbers in relevant files
2. Update CHANGELOG.md with release notes
3. Create a git tag: `git tag -a v1.2.0 -m "Release v1.2.0"`
4. Push tags: `git push origin --tags`
5. Create GitHub release with release notes

## Project Structure Guidelines

When adding new features, follow the existing project structure:

```
├── controllers/           # MVC Controllers
│   ├── auth/             # Authentication related
│   ├── usuarios/         # User management
│   └── permisos/         # Permission management
├── models/               # Data models
├── services/             # Business logic services
├── views/                # View templates
│   ├── layouts/          # Layout components
│   ├── usuarios/         # User views
│   └── permisos/         # Permission views
├── public/
│   ├── js/
│   │   ├── core/         # Core JavaScript utilities
│   │   └── modules/      # Feature-specific JS
│   └── css/
│       ├── core/         # Core styles
│       └── modules/      # Feature-specific CSS
└── config/               # Configuration files
```

## Questions or Issues?

If you have questions about contributing, please:

1. Check existing [Issues](https://github.com/Jandres25/php-mvc-admin-starter/issues)
2. Create a new issue for bugs or feature requests
3. Start a [Discussion](https://github.com/Jandres25/php-mvc-admin-starter/discussions) for questions

## Code of Conduct

This project adheres to a code of conduct. By participating, you are expected to uphold this code:

- Be respectful and inclusive
- Focus on constructive feedback
- Help others learn and grow
- Maintain professional communication

Thank you for contributing to ProyectoBase! 🚀