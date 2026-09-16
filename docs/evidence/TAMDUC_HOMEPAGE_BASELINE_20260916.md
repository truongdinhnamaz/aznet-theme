# Tâm Đức Homepage Baseline — 2026-09-16

**Scope:** authenticated read-only inventory of `https://tamduchanoi.aznet.vn/` for the approved Homepage completion slice.

**Branch:** `ops/tamduc-homepage-completion`  
**Exact head:** `8ad20ef6329d48c99651867dc7ab506fa0c8d581`  
**Workflow run:** `35110312656` — PASS  
**Artifact:** `10452052349` (`tamduc-homepage-inventory`)  
**Digest:** `sha256:ec1e0b76d8fcafe5fc4b2c2227d6abf18ea887be1f2ced9494285c266988cb8f`

## PASS — read-only baseline

- Authenticated inventory completed without site mutation.
- Static safety contract PASS.
- Browser harness syntax PASS.
- WordPress public source inventory PASS.
- Production reports WordPress `7.1`, PHP `8.4.25`, active **AZnet Theme `1.1.0`**, Standalone Core `ready`.
- Homepage Composer preset is `law-01`.
- Primary menu is present.
- Public Homepage has one `<main>` and one H1, with no Theme-owned console/page error observed in this run.

## Observed WordPress/site state

### Site shell

- Site Title: `Tâm Đức Hà Nội`.
- Site Tagline: empty / not observed.
- Custom Logo: `false`.
- Static Front Page: Page `#2`, `Luật sư Tâm Đức - Hà Nội`.
- Front Page featured media: `0`.
- Homepage public `<main>` image count: `0`.
- Public Homepage `tel:`/`mailto:` links: none observed.

### Homepage Content Map

- Services: Page `#122` — `Dịch vụ` → `EMPTY` because no published direct child Page exists.
- About: Page `#34` — `Giới thiệu` → `READY`.
- Team: Page `#142` — current title `Dội ngũ` → `READY`.
- Process: `UNMAPPED`.
- FAQ: `UNMAPPED`.
- Contact: Page `#36` — `Liên hệ` → `READY`.
- Knowledge: Category `#5` — `Kiến thức pháp luật` → `READY` with three published Posts.
- Case analysis: Category `#13` — `Phân tích vụ án` → `EMPTY`.
- Legal news: Category `#1` — `Tin tức` → `EMPTY`.
- Contact provider diagnostic: `READY` through the accepted public Theme integration boundary; this evidence does not inspect provider-private storage and does not claim provider L5.

### Published WordPress content relevant to the Homepage

Published Pages observed: `#2` Home, `#34` Giới thiệu, `#36` Liên hệ, `#122` Dịch vụ, `#142` Dội ngũ, `#12` Trang chủ ConvertFlow, plus Privacy Policy.

Published Posts observed:

- `#147` Cho vay tiền không giấy tờ: Có khởi kiện đòi lại được không? — Category `#14` Hỏi đáp.
- `#144` Ly hôn đơn phương 2026: Hồ sơ, nơi nộp và thời gian giải quyết — Category `#5` Kiến thức pháp luật.
- `#140` Thừa kế không có di chúc: Ai được hưởng và chia thế nào? — Category `#5`.
- `#69` Hợp đồng đặt cọc mua đất có cần công chứng không? — Category `#14`.
- `#64` Thủ tục sang tên sổ đỏ 2026: Hồ sơ, chi phí, thời gian — Category `#5`.

Categories observed:

- `#5` Kiến thức pháp luật — 3 Posts.
- `#14` Hỏi đáp — 2 Posts.
- `#13` Phân tích vụ án — 0 Posts.
- `#1` Tin tức — 0 Posts.

Media images observed in the public WordPress Media API:

- `#14` — `logo ngang` (`logo-ngang.png`).
- `#24` — `cropped-logo-ngang.png`.
- `#13` — `banner 1` (`banner-1.png`).
- `#65` — article image `thu_tuc_sang_ten_so_do.jpg`.

These media labels prove the files exist; they do **not** by themselves authorize an identity/logo or Hero role. No automatic semantic assignment is made from filename/title.

## Public Homepage sections currently rendered

Rendered: Hero, Giới thiệu, Team, native Front Page body, topic navigation, latest Posts, final CTA.

Not rendered because of current source/config state: Services, Process, FAQ, Case Analysis, Legal News.

The Team heading is visibly `Dội ngũ`; the current Homepage contains no `<main>` image and no direct phone/email link.

## BLOCKED / UNKNOWN

- `homepage_law01_variant` is not exposed by the deployed `1.1.0` production bits. Burgundy + Gold exists in newer canonical source, but enabling it would require a separately governed Theme release/deployment. This site-ops slice must not hot-patch or deploy unreleased Theme files.
- No authoritative Homepage role has yet been assigned to Media `#13`, `#14` or `#24`; filename/title is not sufficient to treat an asset as authoritative identity or Hero media.
- No published direct service child Pages exist under Page `#122`.
- No mapped Process or FAQ source exists.
- No public Posts exist in the currently mapped Case Analysis or Legal News categories.
- The read-only baseline does not establish real phone/hotline, email, office address, lawyer roster/credentials, testimonials, awards or case results. Do not fabricate them.

## Ownership classification

Current incompleteness is predominantly WordPress/site-content configuration, not a proven generic Theme runtime defect. Theme presentation continues to fail-soft on unmapped/empty sources as designed.

## Exact next

Build a deterministic mutation plan from authoritative values only. Initial safe candidates are the approved Site Tagline and correction of the observed Page `#142` typo from `Dội ngũ` to `Đội ngũ`. Asset assignment and new substantive business/legal content remain excluded until an authoritative source/role is established.
