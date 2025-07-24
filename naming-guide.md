
# RouteChasm Naming Style Guide

## PHP Naming Conventions

### Namespaces
- Use the folder name as the namespace.
- Use **plural** form for collections: `models`, `modules`, `services`, etc.
- Use **PascalCase** for component/view folders:
  - Example: `Select/Select.php`, `Select/Select.phtml`, `Select/select.js`, `Select/select.css`

### Classes
- **PascalCase**
  - `User`, `DatabaseMigration`, `PasswordField`

### Constants
- **SCREAM_CASE**
  - `MAX_UPLOAD_SIZE`, `DEFAULT_TIMEOUT`

### Variables & Class Methods
- **camelCase**
  - `$userId`, `$formControl`
  - `getData()`, `fetchFromCache()`

### Functions
- **snake_case**
  - `process_input()`, `generate_token()`

## SQL Naming Conventions

### Tables
- **snake_case**, **singular**, with **namespace prefix**
  - `core_user`, `sideloader_cache`

### Columns
- **snake_case**
  - `email`, `created_at`

### Primary/Foreign Keys
- Format: `id_[[table_name]]`
  - Example: `id_user`, `id_post`

### MxN Relations
- Format: `[[table1]]_x_[[table2]]`
  - Example: `user_x_role`, `post_x_tag`

## CSS Naming Conventions

### Classes
- **kebab-case**
  - `.form-control`, `.select-box`, `.modal-overlay`

## JavaScript Naming Conventions

### Variables
- **camelCase**
  - `userData`, `formElement`

### Constants
- **SCREAM_CASE**
  - `MAX_RETRIES`, `DEFAULT_TIMEOUT`

### Functions
- Format: `[[namespace]]_[[function]]`
  - Both in **camelCase**
  - `form_extract()`, `window_open()`, `std_randomInt()`

### Classes
- **PascalCase**
  - `SelectBox`, `RouteHandler`

## Summary Sheet (Quick Reference)

| Type           | Convention          | Example          |
|----------------|---------------------|------------------|
| PHP Class      | PascalCase          | `UserManager`    |
| PHP Constant   | SCREAM_CASE         | `MAX_LENGTH`     |
| PHP Method/Var | camelCase           | `fetchData()`    |
| PHP Function   | snake_case          | `create_token()` |
| PHP Namespace  | folder-based        | `models\User`    |
| SQL Table      | snake_case + prefix | `core_user`      |
| SQL MxN Table  | table_x_table       | `user_x_role`    |
| SQL Column/Key | snake_case          | `id_user`        |
| CSS Class      | kebab-case          | `.input-field`   |
| JS Variable    | camelCase           | `formValue`      |
| JS Constant    | SCREAM_CASE         | `BASE_URL`       |
| JS Function    | namespace_function  | `form_extract()` |
| JS Class       | PascalCase          | `ModalDialog`    |

*(Generated with ChatGPT, modified by CaptSiro)*