# Contributing to Filament Edit Page Tester

Thank you for considering contributing to this package! This document outlines the process for contributing.

## How to Contribute

### Reporting Bugs

If you find a bug, please create an issue on GitHub with:

1. A clear, descriptive title
2. Steps to reproduce the issue
3. Expected behavior
4. Actual behavior
5. Your environment (PHP version, Laravel version, Filament version, Pest version)
6. Any relevant code samples

### Suggesting Enhancements

Enhancement suggestions are welcome! Please create an issue with:

1. A clear description of the enhancement
2. Use cases and benefits
3. Any examples of how it might work
4. Whether you'd be willing to implement it

### Pull Requests

1. **Fork the repository** and create a new branch from `main`

2. **Make your changes** following these guidelines:
   - Follow PSR-12 coding standards
   - Add PHPDoc blocks for all public methods
   - Keep the code readable and maintainable
   - Don't introduce breaking changes without discussion

3. **Write or update tests** if applicable

4. **Update documentation** if you've changed functionality:
   - Update README.md
   - Update CHANGELOG.md
   - Add examples if appropriate

5. **Commit your changes** with clear, descriptive messages:
   ```
   Add support for CustomField component

   - Implement fill method for CustomField
   - Implement preview method for CustomField
   - Implement compare method for CustomField
   - Add tests
   - Update documentation
   ```

6. **Push to your fork** and submit a pull request

7. **Wait for review** - maintainers will review your PR and may request changes

## Development Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/filament-edit-page-tester.git
   cd filament-edit-page-tester
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Run tests (when available):
   ```bash
   ./vendor/bin/pest
   ```

## Code Style

This project follows PSR-12 coding standards. Please ensure your code adheres to these standards.

## Adding Support for New Field Types

To add support for a new Filament field type:

1. **Update FormFiller trait** (`src/Concerns/FormFiller.php`):
   - Add a case in the `match` statement in `fillFormAndSubmit()`
   - Implement the filling logic

2. **Update FormViewer trait** (`src/Concerns/FormViewer.php`):
   - Add a case in the `match` statement in `testPreview()`
   - Implement the preview logic

3. **Update ValuesComparator trait** (`src/Concerns/ValuesComparator.php`):
   - Add a case in the `match` statement in `compareModels()`
   - Implement the comparison logic

4. **Update FilamentSelector** if needed (`src/FilamentSelector.php`):
   - Add any new CSS selector methods required

5. **Update README.md**:
   - Add the new field type to the supported types table
   - Add usage examples if the field type has special behavior

6. **Add tests** demonstrating the new field type works

7. **Update CHANGELOG.md** with the addition

## Adding New Selector Methods

When adding new CSS selectors to `FilamentSelector.php`:

1. Follow the existing naming convention
2. Add PHPDoc comments explaining what the selector targets
3. Test the selector works in a real Filament application
4. Update the "CSS Selector Reference" section in README.md

## Questions?

If you have questions about contributing, feel free to:

- Open an issue for discussion
- Reach out to maintainers

## Code of Conduct

Be respectful, constructive, and professional in all interactions.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
