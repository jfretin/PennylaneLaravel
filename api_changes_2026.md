# 2026 API changes Migration Guide

This full guide is here to help you implement all necessary changes that are coming to the Pennylane API in 2026, including ledger scope changes, pagination updates, and other improvements across multiple endpoints.

The Pennylane API is introducing important changes. This guide explains what's changing and how to migrate your integration smoothly using our phased rollout approach.

:warning:**You will need to have finished the migration by 1st of July 2026 at the latest**. You have in total 24 weeks to complete the migration.

**Important**: To apply the changes, you need to use a header in your API requests. [Here is how to do it](https://pennylane.readme.io/docs/2026-api-changes-guide#-migration-strategy).

***

# 📋 What's Changing

## Scope Deprecation

The `ledger`  scope is being **deprecated** and replaced with more granular scopes:

| Old Scope | New Scopes                                         |
| --------- | -------------------------------------------------- |
| `ledger`  | `journals:readonly` / `journals:all`               |
|           | `ledger_accounts:readonly` / `ledger_accounts:all` |
|           | `ledger_entries:readonly` / `ledger_entries:all`   |
|           | `file_attachments:all`                             |

> **Note:**
>
> The old `ledger` scope will only work on the old behavior system. As soon as you opt-in to the new version, or when the sunset phase start and you didn't explicitely opt-out to the old behavior, `ledger` scope will no longer work.
>
> Important: We have added automatically all new scopes to the OAuth apps, Oauth Access Grant, and Oauth Access Token that were using the ledger scope before **14th of January 2026**.

***

## New Pagination

Some endpoints are using the old page pagination system. We're aligning this scope pagination on the cursor pagination that is used across the rest of V2 endpoints.

Read our complete [Cursor Pagination guide here](https://pennylane.readme.io/docs/using-cursor-based-pagination).

**Affected endpoints:**

* [List journals](https://pennylane.readme.io/reference/getjournals)
* [List Ledger Accounts](https://pennylane.readme.io/reference/getledgeraccounts)
* [List Ledger Entries](https://pennylane.readme.io/reference/getledgerentries)
* [List ledger entry lines of a Ledger Entry](https://pennylane.readme.io/reference/getledgerentriesledgerentrylines)
* [List ledger entry lines lettered to a given ledger entry line ](https://pennylane.readme.io/reference/getledgerentrylinesletteredledgerentrylines)
* [List categories of a Ledger Entry line](https://pennylane.readme.io/reference/getledgerentrylinescategories) (`page` and `per_page` parameters are no longer required)
* [Get the trial balance](https://pennylane.readme.io/reference/gettrialbalance) (`page` and `per_page` parameters are no longer required)
* [List Company's Fiscal Years](https://pennylane.readme.io/reference/company-fiscal-years) (`page` and `per_page` parameters are no longer required)

**Before:**

Following offset pagination params are set :

* `total_pages`
* `current_page`
* `per_page`
* `total_items`

New cursor based pagination attributes are exposed in the response, but set with `null` value :

* `has_more`
* `next_cursor`

*Example:*

```json
{
  "total_pages": 2,
  "current_page": 1, 
  "per_page": 10, 
  "total_items": 11,
  "items": [
    // ...
  ], 
  "has_more": null,
  "next_cursor": null
}
```

**After:**

Following offset pagination params are exposed, but set with `null` value :

* `total_pages`
* `current_page`
* `per_page`
* `total_items`

New cursor based pagination attributes are working:

* `has_more`
* `next_cursor` value is set (or is `null` if you reach the end of the list).

Read our complete [Cursor Pagination guide here](https://pennylane.readme.io/docs/using-cursor-based-pagination) for more details.

*Example:*

```json
{
  "total_pages": null,
  "current_page": null, 
  "per_page": null, 
  "total_items": null,
  "items": [
    // ...
  ], 
  "has_more": true,
  "next_cursor": "eyJpZCI6MTAwfQ=="
}
```

***

## Ledger entries id are not suffixed anymore

The ledger entries ids are currently suffixed with the 3 last digits. This will change in the upcoming version.

**Before:**

A supplier invoice id, for example, will be returned as `12345`, instead of `12345003` with suffix.

**Affected endpoints:**

* Ledger Entries
  * [List Ledger Entries](https://pennylane.readme.io/reference/getledgerentries) (`id` in the response)
  * [Create a ledger entry](https://pennylane.readme.io/reference/postledgerentries) (`id` in the response)
  * [Update a ledger entry](https://pennylane.readme.io/reference/putledgerentries) (`id` in the response, and `id` in the requested params)
  * [Retrieve a Ledger entry](https://pennylane.readme.io/reference/getledgerentry) (`id` in the response)
  * [List ledger entry lines of a Ledger Entry](https://pennylane.readme.io/reference/getledgerentriesledgerentrylines) (`ledger_entry_id` in the requested params)
* Ledger Entry Lines
  * [List ledger entry lines](https://pennylane.readme.io/reference/getledgerentrylines) ( `{ ledger_entry: id }` in the response)
  * [Retrieve a Ledger entry line](https://pennylane.readme.io/reference/getledgerentryline) ( `{ ledger_entry: id }` in the response)
  * [List ledger entry lines lettered to a given ledger entry line](https://pennylane.readme.io/reference/getledgerentrylinesletteredledgerentrylines) ( `{ ledger_entry: id }` in the response)
* In the responses of Customer Invoices:
  * [List customer invoices](https://pennylane.readme.io/reference/getcustomerinvoices)
  * [Retrieve a customer invoice](https://pennylane.readme.io/reference/getcustomerinvoice)
  * [Create a customer invoice](https://pennylane.readme.io/reference/postcustomerinvoices)
  * [Import an invoice with file attached](https://pennylane.readme.io/reference/importcustomerinvoices)
  * [Link a credit note to a customer invoice](https://pennylane.readme.io/reference/linkcreditnote)
  * [Create a customer invoice from a quote](https://pennylane.readme.io/reference/createcustomerinvoicefromquote)
  * [Update a customer invoice](https://pennylane.readme.io/reference/updatecustomerinvoice)
  * [Update an Imported customer invoice](https://pennylane.readme.io/reference/updateimportedcustomerinvoice)
  * [Turn the draft invoice into a finalized invoice](https://pennylane.readme.io/reference/finalizecustomerinvoice)
* In the responses of Supplier Invoices:
  * [List supplier invoices](https://pennylane.readme.io/reference/getsupplierinvoices)
  * [Retrieve a supplier invoice](https://pennylane.readme.io/reference/getsupplierinvoice)
  * [Import a supplier invoice with a file attached](https://pennylane.readme.io/reference/importsupplierinvoice)
  * [Update a supplier invoice](https://pennylane.readme.io/reference/putsupplierinvoice)
  * [Validate the accounting of a supplier invoice](https://pennylane.readme.io/reference/validateaccountingsupplierinvoice)

***

## **Filtering and Sorting Restrictions**

**Breaking Change:** Filtering and sorting by `created_at` and `updated_at` timestamps are being **removed** from ledger-related endpoints.

### **Affected Endpoint**)

The following endpoints will **no longer support** `created_at` or `updated_at` filtering/sorting:

* [List Ledger entries](https://pennylane.readme.io/reference/getledgerentries)

### **What's Being Removed**

**Old behaviour (no longer supported):**

```
# Filtering by created_at - ❌ NO LONGER WORKS
GET /api/external/v2/ledger_entries?filter=[{"field": "created_at", "operator": "gteq", "value": "2025-04-08"}]

# Filtering by updated_at - ❌ NO LONGER WORKS
GET /api/external/v2/ledger_entries?filter=[{"field": "updated_at", "operator": "lteq", "value": "2025-04-08"}]

# Sorting by created_at - ❌ NO LONGER WORKS
GET /api/external/v2/ledger_entries?sort=created_at

# Sorting by updated_at - ❌ NO LONGER WORKS
GET /api/external/v2/ledger_entries?sort=-updated_at
```

### **Migration Strategies**

**Option 1: Use alternative filters**

Instead of `created_at`, use **NEW** filter/sort on `id` (based on unsuffixed ID) or business-relevant `date` fields :

```
# Use ID-based sorting (most recent = highest ID)
GET /api/external/v2/ledger_entries?sort=-id

# For ledger entries, use the date field
GET /api/external/v2/ledger_entries?page=1&per_page=20&filter=[{"field": "date", "operator": "gteq", "value": "2024-01-01"}, {"field": "date", "operator": "lteq", "value": "2024-12-31"}]
```

**Option 2: Client-side filtering**

If you need to track changes:

```bash
# Fetch all records and filter locally
GET /api/external/v2/ledger_entries

# In your application:
# - Cache the last sync timestamp
# - Store the highest ID you've seen
# - On next sync, fetch records with id > last_seen_id

```

**Option 3: Changelogs (recommended for change tracking)**

For real-time updates, consider using changelogs instead of polling with timestamp filters:

* [https://pennylane.readme.io/reference/getledgerentrylinechanges](https://pennylane.readme.io/reference/getledgerentrylinechanges)

***

## Default Sort Order

The default sort order is changing from **ascending** to **descending** on the following endpoints:

* [List journals](https://pennylane.readme.io/reference/getjournals)
* [List Ledger Accounts](https://pennylane.readme.io/reference/getledgeraccounts)
* [List ledger entry lines of a Ledger Entry](https://pennylane.readme.io/reference/getledgerentriesledgerentrylines)

For [List Company's Fiscal Years](https://pennylane.readme.io/reference/company-fiscal-years) endpoint, the default order is changing from `+start` to `-id`.

* `id`, `created_at` and `updated_at` attributes has been added to the response.
* **New** sort query param has been added allowing you to **sort by `id`** or `start`.

**Migration:**

```http
# To maintain current ascending order
GET /api/external/v2/journals?sort=+id

# To use new descending order
GET /api/external/v2/journals?sort=-id

# List Fiscal years : 
# To maintain current start ascending order
GET /api/external/v2/fiscal_years?sort=+start

# To use new descending order
GET /api/external/v2/fiscal_years?sort=-id
```

***

## Default Filtering Removed

The `ledger_entries#index` endpoint will **no longer filter** :

* by open fiscal years\*\* by default.
* draft entries

**What this means:**

* All ledger entries (including the one in **draft** state) will be returned (not just from open current fiscal year)
* The `date` field can now be `null`
* You can still filter explicitly using the `date` parameter

**Migration:**

```http
# If you need to maintain filtering by fiscal year
GET /api/external/v2/ledger_entries?filter=[{"field":"date","operator":"gte","value":"2024-01-01"}]
```

***

## Response Structure Changes

We're improving our API responses by exposing full objects instead of simple ID references:

#### Journal Object

**Before:**

```json
{
  "journal_id": 42
}
```

**After:**

```json
{
  "journal": {
    "id": 42,
    "url": "https://app.pennylane.com/api/external/v2/journals/42"
  }
}
```

**Affected endpoints:**

* [List Ledger Entries](https://pennylane.readme.io/reference/getledgerentries)
* [Create a ledger entry](https://pennylane.readme.io/reference/postledgerentries)
* [Update a ledger entry](https://pennylane.readme.io/reference/putledgerentries)

***

#### Ledger Account Object

**Before:**

```json
{
  "ledger_account_id": 42
}
```

**After:**

```json
{
  "ledger_account": {
    "id": 42,
    "number": "401000",
    "url": "https://app.pennylane.com/api/external/v2/ledger_accounts/42"
  }
}
```

**Affected endpoints:**

* [Create a ledger entry](https://pennylane.readme.io/reference/postledgerentries)
* [Update a ledger entry](https://pennylane.readme.io/reference/putledgerentries)
* [List ledger entry lines of a Ledger Entry](https://pennylane.readme.io/reference/getledgerentriesledgerentrylines)

***

#### Attachment Object

In the response, `ledger_attachment_id` is replaced with `attachment`

**Before:**

```json
{
  "ledger_attachment_id": 42,
  "ledger_attachment_filename": "receipt.pdf"
}
```

**After:**

```json
{
  "attachment": {
    "filename": "receipt.pdf",
    "url": "https://..."
  }
}
```

**Request parameter change:**

* **Old:** `ledger_attachment_id`
* **New:** `file_attachment_id`

**Affected endpoints:**

* [Create a ledger entry](https://pennylane.readme.io/reference/postledgerentries)
* [Update a ledger entry](https://pennylane.readme.io/reference/putledgerentries)
* [List Ledger Entries](https://pennylane.readme.io/reference/getledgerentries) (deprecating `ledger_attachment_filename` is present and deprecated)

***

## Endpoint Deprecation

**Deprecated:** `POST /api/external/v2/ledger_attachments` (Upload a file)

**Use instead:** `POST /api/external/v2/file_attachments` (Upload a file)

> **Important:** You'll need the `file_attachments:all` scope to use the new endpoint.

***

## Error Message Improvements

Error messages for 404 responses have been modified:

**Affected endpoints:**

* [Retrieve a journal](https://pennylane.readme.io/reference/getjournal#/)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find Journal with 'id'=123"
}
```

* [Get a ledger account](https://pennylane.readme.io/reference/getledgeraccount#/)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find LedgerAccount with 'id'=123"
}
```

* [Retrieve a Ledger entry](https://pennylane.readme.io/reference/getledgerentry)
* [Update a ledger entry](https://pennylane.readme.io/reference/putledgerentries#/)
* [List ledger entry lines of a Ledger Entry](https://pennylane.readme.io/reference/getledgerentriesledgerentrylines#/)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find LedgerEntry with 'id'=123"
}
```

* [Retrieve a Ledger entry line](https://pennylane.readme.io/reference/getledgerentryline#/)
* [List ledger entry lines lettered to a given ledger entry line](https://pennylane.readme.io/reference/getledgerentrylinesletteredledgerentrylines#/)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find LedgerEntryLine with 'id'=123"
}
```

* [Retrieve a company customer](https://pennylane.readme.io/reference/getcompanycustomer)
* [Update a company customer](https://pennylane.readme.io/reference/putcompanycustomer)
* [Retrieve an individual customer](https://pennylane.readme.io/reference/getindividualcustomer)
* [Update an individual customer](https://pennylane.readme.io/reference/putindividualcustomer)
* [Retrieve a customer](https://pennylane.readme.io/reference/getcustomer)
* [Update a billing subscription](https://pennylane.readme.io/reference/putbillingsubscriptions)
* [Create a SEPA mandate](https://pennylane.readme.io/reference/postsepamandates)
* [Update a SEPA mandate](https://pennylane.readme.io/reference/putsepamandate)
* [Associate a GoCardless mandate to a customer](https://pennylane.readme.io/reference/postgocardlessmandateassociations)
* [Send a GoCardless mandate email request](https://pennylane.readme.io/reference/postgocardlessmandatemailrequests)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find Customer with 'id'=123"
}
```

* [Retrieve a supplier](https://pennylane.readme.io/reference/getsupplier)
* [Update a supplier](https://pennylane.readme.io/reference/putsupplier)

```json
# NOW
{
  "status": 404,
  "error": "Couldn't find Supplier with 'id'=123"
}
```

***

# 🚀 Migration Strategy

We're using a **three-phase rollout** to ensure a smooth transition:

## Phase 1: Preview Phase (12 weeks)

**Timeline:** Starting 14th of January 2026

**Default Behavior:** Old behavior

**Action Required:** Opt-in to test and migrate to the new behavior

During this phase, the new behavior is **opt-in only**. This is your opportunity to test the changes without any risk.

**How to opt-in:**

Using headers (recommended for production):

```bash
curl -H "X-Use-2026-API-Changes: true" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  https://api.pennylane.com/api/external/v2/ledger_entries
```

Using query parameters (easier for testing):

```bash
curl "https://api.pennylane.com/api/external/v2/ledger_entries?use_2026_api_changes=true" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

***

## Phase 2: Sunset Phase (12 weeks)

**Timeline:** Starting 8th of April 2026

**Default Behavior:** New behavior ⚠️ **BREAKING CHANGE**

**Action Required:** Opt-out if you need more time to migrate

During this phase, the new behavior becomes the **default**. If you're not ready, you can temporarily opt-out.

**How to opt-out:**

Using headers (recommended for production):

```bash
curl -H "X-Use-2026-API-Changes: false" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  https://api.pennylane.com/api/external/v2/ledger_entries
```

Using query parameters (easier for testing):

```bash
curl "https://api.pennylane.com/api/external/v2/ledger_entries?use_2026_api_changes=false" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

> **Warning:** This is a temporary safety valve. You must complete your migration before the Cleanup phase.

:warning: If both header and query parameter are provided with conflicting values, you'll receive a `400 Bad Request` error. Please use one or the other.

***

## Phase 3: Cleanup Phase (Permanent)

**Timeline:** At the end of the sunset phase, **on 1st of July 2026.**

**Default Behavior:** New behavior only

During this phase:

* All compatibility code is removed
* The opt-in/opt-out mechanism is disabled
* Only the new behavior exists

**You must have migrated by this point.**

***

## 📊 Behavior by Phase

| Phase \ Header value | No Header                  | `true`           | `false`          |
| -------------------- | -------------------------- | ---------------- | ---------------- |
| **Preview**          | **OLD** behavior           | **New** behavior | **OLD** behavior |
| **Sunset**           | :warning: **New** behavior | **New** behavior | **OLD** behavior |
| **Cleanup**          | **New** behavior           | **New** behavior | **New** behavior |

***

## 🎯 Recommended Migration Paths

### Option A: Early Adoption (Recommended)

1. **Week 1-2:** Set `X-Use-2026-API-Changes: true` in your testing environment
2. **Week 2-8:** Test thoroughly with new behavior
3. **Week 8+:** Deploy to production with header `X-Use-2026-API-Changes` set to `true`
4. **Week 12:** No action needed when Sunset phase starts ✅

**Benefits:**

* Zero disruption during Sunset phase
* More time for thorough testing
* No emergency fixes needed

***

### Option B: Proactive Opt-Out

1. **Before Week 12:** Set `X-Use-2026-API-Changes: false` in production
2. **Week 12:** Sunset phase starts (no disruption, header keeps old behavior)
3. **Week 12+:** Complete migration in your testing environment removing the `X-Use-2026-API-Changes` header
4. **Before Week 24:** Deploy to production and remove header ✅

**Benefits:**

* Extra time to prepare

***

### Option C: Reactive Opt-Out (Emergency Only)

1. **Week 12:** Discover breaking change when Sunset starts
2. **Week 12 (Day 1):** Immediately set `X-Use-2026-API-Changes: false`
3. **Week 12+:** Rush to complete migration in your testing environment removing the `X-Use-2026-API-Changes` header.
4. **Week 24:** Deploy to production and remove header ✅

**Warning:** High-stress migration path. Not recommended.

***

## 🧪 Testing Your Integration

### Testing new behavior during Preview Phase

```bash
# Test new behavior
curl -H "X-Use-2026-API-Changes: true" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://api.pennylane.com/api/external/v2/ledger_entries

# Verify that new behavior is available: 
# - Test the new Cursor base pagination system
# - Ledger Entries ID are not suffixed anymore with 3 digits ("00X")
# - Not found "new" error message
# - Check default sort order
# - Test filter on Ledger Entry ID (created_at and updated_at renders an error)
# - Test that Ledger Entries outside of open fiscal period are rendered by default
```

### Testing Old behavior during Sunset Phase

```bash
# Test that you can revert if needed
curl -H "X-Use-2026-API-Changes: false" \
     -H "Authorization: Bearer YOUR_TOKEN" \
     https://api.pennylane.com/api/external/v2/ledger_entries

# Verify old behavior is restored
# - Deprecated Offset Pagination system
# - Ledger Entries ID are suffixed with 3 digits ("00X")
# - Not found "old" error message
# - Check default sort order 
# - Filter on Ledger Entry ID is not allowed, created_at and updated_at works
# - Test that Ledger Entries outside of open fiscal period are NOT rendered by default
```

***

# ❓ FAQ

### When will these changes take effect?

The Preview phase begins on **14th of January 2026**.

The new behavior is adopted across all endpoints by default on the **8th of April 2026**.

**By 1st of July 2026, no rollback is possible.** You have in total 24 weeks to complete the migration.

### Do I need to update my OAuth scopes immediately?

The old `ledger` scope will only work on the old behavior system. As soon as you opt-in to the new version, or when the sunset phase start and you didn't explicitely opt-out to the old behavior, `ledger` scope will no longer work.

Important: We have added automatically all new scopes to the OAuth apps, Oauth Access Grant, and Oauth Access Token that were using the ledger scope before **14th of January 2026**.

### I have a developer token, what do I need to do?

If you already have a developer token generated before **14th of January 2026**  with the ledger scope, you have nothing to do. If you need to generate a new token, make sure you select the new scopes.

### What happens if I don't set any header?

* **Preview phase:** You'll get the old behavior (safe)
* **Sunset phase:** You'll get the new behavior (⚠️ breaking change)
* **Cleanup phase:** You'll get the new behavior (only option)

### Can I use both headers and query parameters?

Yes, but they must have the same value. If they conflict, you'll receive a `400 Bad Request` error.

### How long do I have to migrate?

You have approximately **24 weeks** from the start of the Preview phase until the Cleanup phase begins.

### What if I need help?

Contact our support team !

:warning:We will not do any support through readme discussions space.

### Will this affect the Firm API?

No, this is only about the V2 API.

***

## 📅 Timeline Summary

```
┌───────────────────────────────────────────────────────────┐
│                                                           │
│             Week 1          Week 12          Week 24      │
│               │                │               │          │
│               │                │               │          │
│               ▼                ▼               ▼          │
│         Preview Phase    Sunset Phase    Cleanup Phase    │
│           (12 weeks)      (12 weeks)      (Permanent)     │
│                                                           │
│  Default:  Old Behavior    New Behavior    New Behavior   │
│  Action:      Opt-in         Opt-out          N/A         │
│                                                           │
└───────────────────────────────────────────────────────────┘
```

***

## 📝 Migration Checklist

Use this checklist to track your migration progress:

* [ ] Review all affected endpoints in your integration
* [ ] Update OAuth scopes from `ledger` to granular scopes, or generate a new developer token if necessary
* [ ] Test with `X-Use-2026-API-Changes: true` in staging
* [ ] Update code to use new object structures (`journal`, `ledger_account`, `attachment`)
* [ ] Update code to use `file_attachment_id` instead of `ledger_attachment_id`
* [ ] Rely on `ledger_entry` **unsuffixed** ID in response and request.
* [ ] Rely on the new cursor base pagination system
* [ ] Add explicit `sort` parameter if you rely on ascending order
* [ ] Add explicit `date` filtering if you rely on fiscal year filtering
* [ ] Update error handling for new 404 error messages
* [ ] Test all ledger-related API calls
* [ ] Deploy to production with header set
* [ ] Monitor for errors after Sunset phase begins
* [ ] Remove header after Cleanup phase (optional, but recommended)

***

## Need Help?

If you have questions or need assistance with your migration, contact our support team for help.

:warning: No support will be done from the Discussions space, as it's not efficient for personalized assistance.

We're here to help ensure a smooth transition! 🚀