# E5 RootProfile Current-Surface Integration Contract — Design

**Date:** 2026-09-02  
**Status:** Approved architecture; implementation not yet started  
**AZnet Theme baseline:** E4 verified production tree, `main` commit `b1e21bf5fb61be66f9c77c038fc16b5fd0dfceb7`  
**RootProfile inspected baseline:** `RootProfile-0.1.0-alpha.116.zip`  
**Scope:** Cross-product request-context contract required before AZnet Theme can safely own Profile/Contact presentation at runtime.

## 1. Decision

Adopt a new RootProfile-owned, public, same-process, versioned request-context contract:

`rootprofile/presentation/current-surface/v1`

The contract answers one bounded question only:

> On this frontend request, does RootProfile recognize a public presentation surface, and if so, what normalized public-safe presentation payload belongs to that surface?

The contract reports **context, not production-render authorization**. A valid current-surface payload never means AZnet Theme may silently replace the current renderer. Production takeover remains a separate, explicit, later gate.

RootProfile remains the only owner of routing, URL/canonical resolution, mapped Page resolution, public eligibility, Entity resolution and authoritative profile/contact semantics. AZnet Theme receives only the resolved public presentation context. The Theme must not re-resolve RootProfile routes, inspect RootProfile storage, infer surfaces from slugs/titles, or call RootProfile private/internal classes.

This contract is additive. Existing presentation provider v1 and v2 contracts remain unchanged.

## 2. Why E5 needs this contract

AZnet Theme E4 already contains presentation-only renderers for:

- RootProfile Contact Surface via provider v1;
- RootProfile Person Profile via provider v2;
- RootProfile Organization Profile via provider v2.

However, the Theme cannot safely decide which RootProfile surface owns the current request:

- provider v2 `person_profile` requires a RootProfile entity ID;
- RootProfile currently resolves Person IDs through internal `Router::currentPersonId()` logic;
- dedicated Person/Organization routes are currently returned by RootProfile `template_include` and rendered by RootProfile templates;
- mapped Person/Organization Page behavior and Contact mapped-Page behavior are also resolved inside RootProfile;
- Contact mapping is explicit and must not be rediscovered by slug/title/similarity;
- canonical/profile URL ownership must remain in RootProfile.

Therefore Theme-side routing heuristics or direct calls to RootProfile internals would violate the approved ecosystem rule: **Source owns data. Theme owns presentation. Integration contracts connect them.**

## 3. Ownership boundary

### 3.1 RootProfile owns

RootProfile continues to own and resolve:

- Person and Organization identity;
- Entity UUID;
- Person/Organization public eligibility;
- Profile Surface mapping;
- Contact Surface explicit mapped Page;
- dedicated route/query-var semantics;
- author-archive mapping where applicable;
- canonical/profile URL resolution and redirects;
- presentation/composition state that is already RootProfile-owned for compatibility;
- public-safe projection eligibility;
- current request → current RootProfile surface resolution;
- provider v1/v2 payload production;
- fallback rendering when AZnet Theme does not take presentation ownership.

### 3.2 AZnet Theme owns

AZnet Theme may own, after the required activation gate passes:

- Profile/Contact template composition;
- Theme layout and site shell;
- typography, spacing, cards, grids and responsive behavior;
- Theme-owned CSS/assets;
- rendering of normalized RootProfile provider payloads;
- fail-soft omission when RootProfile or the current-surface contract is unavailable.

### 3.3 Forbidden Theme behavior

AZnet Theme must not:

- call `TruongDinhNam\\RootProfile\\Surfaces\\Router`;
- read RootProfile option/meta/table/CPT/private classes to discover a current surface;
- derive Person entity IDs from `/ho-so/{slug}` itself;
- detect Contact by page slug/title/content similarity;
- infer Organization canonical identity from Contact Surface;
- treat current-surface availability as takeover authorization;
- reinterpret RootProfile `none|external|studio` storage values inside Theme;
- mutate RootProfile presentation owner, mapping, Page content, slug/title, builder metadata or canonical state merely to render;
- create parallel RootProfile identity/contact/profile storage.

## 4. Contract shape

### 4.1 Hook

RootProfile registers:

```php
rootprofile/presentation/current-surface/v1
```

Suggested RootProfile constants:

```php
HOOK     = 'rootprofile/presentation/current-surface/v1'
CONTRACT = 'rootprofile.current_surface'
VERSION  = 1
```

AZnet Theme consumes it through `apply_filters()` in the same fail-soft style as provider v1/v2.

### 4.2 Consumer call

The consumer passes only the current accumulated value. It does not pass slug, Page ID, RootProfile entity ID or guessed resource:

```php
$context = apply_filters(
    'rootprofile/presentation/current-surface/v1',
    null
);
```

This keeps route and mapping resolution entirely inside RootProfile.

### 4.3 Successful payload

A successful payload has this normalized shape:

```php
[
    'contract' => 'rootprofile.current_surface',
    'version' => 1,
    'surface' => 'person_profile|organization_profile|contact',
    'presentation' => [
        // Existing provider payload, unchanged:
        // - provider v2 payload for person_profile / organization_profile
        // - provider v1 payload for contact
    ],
]
```

No RootProfile internal entity ID, post ID, mapped Page ID, query-var value, option/meta key, table name, repository path, presentation-owner storage value or private implementation detail may cross this contract.

### 4.4 No recognized eligible surface

Return `null` when:

- the request is not a RootProfile-recognized public presentation surface;
- the surface is not publicly eligible for the current request context;
- provider projection fails;
- required public projection is malformed/unavailable;
- an internal exception occurs.

`null` means **no authoritative RootProfile current-surface context**. It never means the Theme should reverse-engineer a fallback RootProfile identity.

### 4.5 Context is not authorization

A non-null result means only that RootProfile recognizes the current request and can supply its public presentation model.

It does **not** mean:

- RootProfile has switched production presentation owner;
- AZnet Theme may replace Page content or RootProfile fallback output;
- an existing `external` Page should be silently converted into Theme-composed RootProfile presentation;
- an existing `studio` renderer should be bypassed;
- canonical routing or redirect behavior changes.

Any later production takeover mechanism must be specified and activated separately. E5-A/E5-B must not invent that mechanism.

## 5. RootProfile resolution behavior

The contract implementation may reuse RootProfile internals because it is implemented inside RootProfile. It must preserve current route/canonical behavior rather than duplicate it.

Resolution order must prevent overlapping contexts and preserve current product semantics:

1. **Person Profile**
   - recognize only when RootProfile's existing request resolver says a Person Profile is the current valid/public surface;
   - project with existing provider v2 `person_profile` source;
   - do not expose the internal Person ID to the Theme.

2. **Organization Profile**
   - recognize only when RootProfile's existing request resolver says the canonical/mapped Organization Profile is the current valid/public surface;
   - project with provider v2 `organization_profile`;
   - preserve canonical/profile URL ownership in RootProfile.

3. **Contact Surface**
   - recognize only the explicit RootProfile mapped Contact Page under current Contact Surface mapping/public-projection rules;
   - project with provider v1 `contact`;
   - never treat Contact as canonical Organization Profile;
   - never auto-map by slug, title or content similarity.

Current-surface recognition is independent from whether RootProfile currently renders that surface through `external` or `studio` presentation. Those values remain RootProfile compatibility/presentation state and do not cross the new contract.

The implementation must use one RootProfile-owned request-context resolver/adaptor rather than asking AZnet Theme to understand the three internal routing systems.

## 6. Presentation claim and activation gate

### 6.1 Contract availability is not takeover activation

Adding the current-surface contract does **not** by itself transfer production rendering to AZnet Theme.

The contract is a read-only integration capability. Existing RootProfile/WordPress fallback/current rendering remains valid until the LIVE WordPress UAT, destination-rendering and explicit activation design/gates pass.

### 6.2 E5 staged activation

E5 is divided into bounded checkpoints:

**E5-A — RootProfile current-surface contract**

- RootProfile publishes current-surface context.
- Existing provider v1/v2 and rendering behavior remain unchanged.
- No Theme runtime takeover claim is introduced.

**E5-B — AZnet Theme consumer + dormant dispatcher compatibility**

- AZnet Theme can consume and validate current-surface context.
- Existing E2/E3/E4 renderers can be dispatched from an explicit function/test call.
- The dispatcher is not wired to `template_include`, `the_content` or another automatic production interception point.
- Generic Page/Post behavior remains unchanged.

**E5-C — LIVE database-backed UAT**

- verify three-way compatibility and destination rendering using explicit test wiring/preview where appropriate;
- collect canonical/schema/theme-switch/accessibility/rollback evidence.

**E5-D — production presentation takeover**

- requires its own explicit activation design based on E5-C evidence;
- must preserve RootProfile fallback/rollback;
- must not silently reinterpret existing presentation-owner state.

Do not infer E5-D PASS from E5-A, E5-B or E5-C.

## 7. AZnet Theme consumer design

Add the minimum adapter to the existing RootProfile integration module.

Suggested Theme API:

```php
current_surface_context(): ?array
```

Validation rules:

- exact `contract` match;
- exact version `1`;
- surface allow-list: `person_profile`, `organization_profile`, `contact`;
- `presentation` must be an array;
- nested presentation payload must also pass the existing provider contract/version/resource validation appropriate to its surface;
- any exception, unsupported version or malformed payload returns `null`.

The adapter should return a presentation-safe Theme context, not raw unvalidated input.

Suggested dormant render dispatcher, kept separate from route ownership and production interception:

```php
render_current_rootprofile_surface(array $context): bool
```

Behavior when called explicitly:

- `person_profile` → existing Theme Profile Surface renderer;
- `organization_profile` → existing Theme Profile Surface renderer;
- `contact` → existing Theme Contact Surface renderer;
- unsupported/malformed context → render nothing and return `false`;
- enqueue only the assets needed for the recognized surface.

The dispatcher does not inspect WordPress slug/query vars to identify RootProfile surfaces and is not auto-invoked during E5-B.

## 8. Fail-soft and error handling

All integration boundaries fail soft:

- RootProfile absent → Theme remains a valid WordPress theme;
- current-surface hook absent → no authoritative current-surface context;
- provider v1/v2 absent or unsupported → no authoritative Theme fallback from RootProfile storage;
- provider/current-surface exception → return `null` and preserve existing request behavior;
- malformed payload → reject; do not partially render authoritative identity;
- Theme absent → RootProfile current/fallback renderer remains usable;
- generic theme → RootProfile remains usable;
- runtime presentation failure after any future takeover activation must have an explicit rollback/fallback path; it must not corrupt mapping or authoritative state.

## 9. Compatibility requirements

The contract must preserve the following combinations:

1. RootProfile + AZnet Theme;
2. RootProfile + generic compatible theme;
3. AZnet Theme without RootProfile;
4. unsupported/missing provider version;
5. unsupported/missing current-surface contract;
6. provider/current-surface exception;
7. theme switch AZnet Theme → generic theme → AZnet Theme;
8. existing mapped Organization Profile Page;
9. dedicated Organization Profile route;
10. dedicated Person Profile route;
11. mapped Person Profile Page where supported by current RootProfile behavior;
12. author-archive Person surface where supported by current RootProfile behavior;
13. explicit mapped Contact Surface Page;
14. non-mapped normal WordPress Page whose slug/title resembles Contact/Profile;
15. current RootProfile `external` and `studio` presentation states remain behavior-compatible during E5-A/E5-B.

## 10. Security and privacy constraints

Current-surface v1 is a public presentation contract, so it must be recursively public-safe.

It must not expose:

- internal Person/Organization post IDs;
- linked WordPress user IDs;
- Claim/Evidence internal IDs;
- section record IDs/UUIDs except the authoritative public Entity UUID already allowed by provider contracts;
- mapped WordPress Page IDs;
- attachment/content storage IDs;
- option/meta/table names;
- private contact/evidence fields;
- presentation-owner storage keys/values;
- repository/internal class details.

The contract should compose or reuse the existing provider v1/v2 sanitized payloads rather than creating a second serialization of domain truth.

## 11. TDD / verification design

### 11.1 RootProfile RED tests

Before implementation, tests must fail for at least:

- hook not registered;
- recognized dedicated Person request returns no current-surface context;
- recognized Organization request returns no current-surface context;
- mapped Contact request returns no current-surface context;
- public contract leaks an internal entity/Page ID;
- unrecognized normal Page is incorrectly claimed;
- provider failure is not fail-soft.

### 11.2 RootProfile GREEN contract tests

Verify:

- contract/version/surface are exact;
- Person context embeds provider v2 `person_profile` payload;
- Organization context embeds provider v2 `organization_profile` payload;
- Contact context embeds provider v1 `contact` payload;
- no surface is inferred by slug/title similarity;
- invalid/private/non-public requests return `null`;
- current canonical/redirect behavior remains owned by RootProfile;
- current `external|studio` runtime presentation behavior is unchanged;
- v1 and v2 existing providers remain behavior-compatible.

### 11.3 AZnet Theme RED/GREEN tests

Verify:

- Theme rejects missing hook;
- Theme rejects wrong contract/version;
- Theme rejects unsupported surface;
- Theme rejects malformed nested provider payload;
- Theme accepts each valid surface and explicit dormant dispatch selects the correct existing renderer;
- no production interception hook is added in E5-B;
- Theme never calls RootProfile internal classes/storage;
- Theme with no RootProfile remains non-fatal;
- generic Page/Post behavior remains unchanged.

### 11.4 Runtime/UAT gate

Production takeover remains **UNKNOWN/PENDING** until database-backed WordPress UAT verifies:

- `/gioi-thieu/` or the actual Organization canonical surface;
- at least one `/ho-so/{slug}` Person surface;
- actual mapped Contact Surface;
- canonical/profile URLs unchanged;
- no duplicate Organization/Person schema/identity;
- no data loss through theme switch;
- RootProfile generic-theme fallback remains usable;
- AZnet Theme without RootProfile remains usable;
- desktop/mobile smoke;
- keyboard navigation/accessibility smoke;
- provider failure/contract failure fail-soft;
- documented rollback.

Passing this UAT establishes evidence for a later E5-D activation decision; it still does not silently activate production takeover.

## 12. Implementation boundaries

Expected RootProfile E5-A change is intentionally small and adapter-oriented:

- one current-surface public contract/provider class;
- registration in RootProfile bootstrap/plugin wiring;
- focused contract tests;
- reuse of existing `Router`/surface resolvers and PresentationProvider v1/v2 projection, without moving domain ownership.

Expected AZnet Theme E5-B change is intentionally small:

- current-surface consumer/validator in `inc/integrations/rootprofile.php` or one focused adjacent integration file;
- one dormant presentation dispatcher;
- focused tests/verification;
- no automatic route/content interception;
- no rewrite of E2/E3/E4 renderers.

If implementation reveals that E5-A/E5-B requires changing canonical routing, RootProfile storage vocabulary, Profile Provider v2 schema, Contact mapping semantics, or production presentation-owner activation, stop and reclassify that work as a later architectural decision. Those changes are outside E5-A/E5-B.

## 13. Recovery checkpoints

Execution must preserve recoverability:

1. **Checkpoint E5-A:** RootProfile current-surface contract tests PASS; no Theme runtime takeover claim.
2. **Checkpoint E5-B:** AZnet Theme consumer/dormant-dispatcher compatibility tests PASS; generic Theme behavior unchanged.
3. **Checkpoint E5-C:** database-backed three-way UAT evidence recorded; production takeover still not inferred.
4. **Checkpoint E5-D:** production presentation takeover is a separately approved/verified activation slice with rollback evidence.

Do not mark E5 as globally PASS when only an earlier checkpoint passes.

## 14. Explicit non-goals

E5-A/E5-B does not:

- redesign Profile/Contact visuals;
- change the 17 Business Profile Core Sections;
- create a new Contact domain;
- change canonical URLs;
- migrate Person/Organization storage;
- create a new public CPT;
- replace provider v1/v2;
- add a Theme-side RootProfile database adapter;
- remove RootProfile fallback rendering;
- reinterpret `none|external|studio` in Theme;
- activate presentation takeover before later evidence and approval.

## 15. Acceptance criteria for the design

The design is ready for implementation planning when all of the following are agreed:

- current request resolution stays inside RootProfile;
- the public hook is `rootprofile/presentation/current-surface/v1`;
- contract is `rootprofile.current_surface`, version `1`;
- payload carries `surface` plus an already normalized existing-provider `presentation` payload;
- no internal IDs/mapping/storage/presentation-owner details cross the boundary;
- Theme uses no slug/title heuristic and no RootProfile private class;
- current-surface context is explicitly not takeover authorization;
- E5-B dispatcher remains dormant/not auto-wired;
- RootProfile/WordPress existing presentation behavior remains unchanged through E5-A/E5-B;
- RootProfile fallback remains until a later takeover/rollback gate passes;
- E5 is executed as bounded checkpoints, not one inferred global PASS.
