## 📋 Feature Overview

**Requirement ID:** **Description:** ## 🏗 Architectural Alignment

-   [ ] **Multi-Tenancy**: Uses `BelongsToOrganization` trait and queries are properly scoped.
-   [ ] **Service Layer**: Business logic is encapsulated in a Service class with constructor property promotion.
-   [ ] **Type Safety**: `declare(strict_types=1);` is present, and all methods have explicit parameter/return types.
-   [ ] **Models**: Uses `casts()` method (Laravel 12 style) and has a corresponding Factory/Seeder.

## ⚡ Performance & Frontend

-   [ ] **N+1 Prevention**: Eager loading (`with()`) is implemented for all relationships.
-   [ ] **Livewire**: Single root element used; `wire:key` present in loops; `wire:loading` implemented.
-   [ ] **Assets**: Verified with `npm run build`.

## 🧪 Testing & QA (Mandatory)

-   [ ] **Unit/Feature Tests**: Pest/PHPUnit tests pass with 85%+ coverage.
-   [ ] **Dusk Tests**: Browser tests added and pass via `composer run test-dusk`.
-   [ ] **Full Cycle**: Successfully ran `composer run dev-cp-complete`.
-   [ ] **Database**: Migrations include `down()` methods and have been tested on populated DBs.

## 📄 Documentation

-   [ ] **PHPDoc**: All classes/methods have DocBlocks with array shapes defined where applicable.
-   [ ] **Feature Guide**: Implementation guide added to `docs/features/complete/`.
-   [ ] **Audit Trail**: Significant business actions are being logged.

---

### 📸 Screenshots / UI Changes
