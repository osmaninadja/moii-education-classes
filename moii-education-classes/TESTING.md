# Testing Documentation for moii-education-classes

## How to run tests locally

From the package directory:
```bash
cd packages/moii-education-classes
../../vendor/bin/phpunit
```

## How to run with coverage

```bash
../../vendor/bin/phpunit --coverage-html=coverage
```

## Test structure and what each test file covers

### Feature/ClassControllerTest.php
Comprehensive HTTP/feature tests covering:
- Authentication (401 responses for unauthenticated requests)
- Authorization (403 responses for missing permissions)
- CRUD operations for classes (index, show, create, update, delete)
- Special endpoints (active classes, grade filtering, capacity status)
- Enrollment management (create, list, delete, bulk operations)
- Schedule management (create, list, update, delete, day filtering)
- Rate limiting (429 responses when limits exceeded)
- Input validation (422 responses for invalid data)
- Tenant isolation (404 responses for cross-tenant access)
- UUID validation (422 responses for invalid UUID formats)

### Feature/ClassTest.php
Service-layer unit tests covering:
- Capacity constraint enforcement
- Duplicate enrollment prevention
- Schedule conflict detection
- Immutable field protection
- Historical enrollment tracking

## How to add new tests

1. For HTTP tests, add methods to `ClassControllerTest.php` following the naming convention `test_[action]_[condition]_[expected_result]`
2. For service tests, add methods to `ClassTest.php` with descriptive names
3. Use factories for test data creation
4. Always use `RefreshDatabase` trait for clean state
5. Test both positive and negative scenarios
6. Include tenant context in multi-tenant tests

## Test data

Tests use Laravel factories for consistent data generation. Ensure factories are properly configured with required traits and relationships.