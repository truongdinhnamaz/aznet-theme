**AZNET THEME**

Mục lục Nguồn và Governance

Quy định source ownership, thứ tự ưu tiên tài liệu và cơ chế thay đổi của dự án AZnet Theme.

| **Mã tài liệu** | AZT-00 | **Phiên bản** | v0.4 |
| --- | --- | --- | --- |
| **Trạng thái** | Working Source | **Ngày** | 07/09/2026 |

| **Quy tắc gốc: **Source owns data. Theme owns presentation. Integration contracts connect them. Mọi tài liệu và quyết định của AZnet Theme phải bảo toàn ranh giới này. |
| --- |

# 1. Mục đích của bộ tài liệu

AZnet Theme cần một bộ nguồn đủ rõ để nhiều phiên phát triển có thể tiếp tục mà không tái tranh luận những quyết định đã chốt, không copy nhầm domain logic sang theme và không mất provenance của presentation code đã tích lũy.

- Ghi rõ tài liệu nào có quyền quyết định vấn đề nào.
- Tách quyết định kiến trúc khỏi bằng chứng implementation và khỏi đề xuất chưa chốt.
- Buộc mọi thay đổi ownership hoặc contract phải có Decision Log và đánh giá tác động.
- Cho phép theme tiến hóa độc lập nhưng không biến thành hard dependency của ConvertFlow, RootProfile hoặc WooCommerce.

# 2. Bộ tài liệu nguồn AZnet Theme

| **STT** | **Tài liệu** | **Source owner cho** | **Trạng thái** |
| --- | --- | --- | --- |
| 00 | Mục lục Nguồn và Governance | Thứ tự ưu tiên nguồn, change control, thuật ngữ quản trị | Working Source |
| 01 | Product Charter và Ownership | Sứ mệnh sản phẩm, phạm vi, non-goals, surface ownership | Working Source |
| 02 | Kiến trúc Theme và Integration Contracts | Layering, dependency policy, provider contracts, fail-soft | Working Source |
| 03 | Current Baseline và Code Provenance | Snapshot registry, inventory schema, extraction/migration evidence | Working Source |
| 04 | Roadmap, QA và Decision Log | Milestones, acceptance, test matrix, decisions và open questions | Working Source |
| **05** | **Hiến pháp sản phẩm** | Bất biến sản phẩm, product independence, quality floor, release constitution, amendment rule | Approved Constitution |

# 3. Thứ tự ưu tiên khi nguồn mâu thuẫn

1. Dữ liệu/domain semantics thuộc hệ thống nào thì source owner của hệ thống đó có quyền cao nhất. AZnet Theme không tự định nghĩa lại identity, conversion, commerce hoặc WordPress native content ownership.
2. Trong phạm vi riêng của Theme, AZT-05 Hiến pháp sản phẩm có quyền cao nhất đối với bất biến sản phẩm và release constitution. AZT-01-04 tiếp tục là source owner theo đúng chủ đề và phải phù hợp AZT-05.
3. Technical handoff là ràng buộc bàn giao và provenance reference. Nó không được dùng để ghi đè tài liệu source owner mà chính handoff đã dẫn chiếu.
4. Code/package snapshot là bằng chứng implementation. Khi code mâu thuẫn với kiến trúc đã chốt, không mặc nhiên coi code là đúng; phải xác định đó là legacy, adapter, bug hay quyết định chưa được phản ánh vào tài liệu.
5. Đề xuất chưa chốt chỉ được ghi ở Decision Log với trạng thái Proposed/Open, không được viết như yêu cầu bắt buộc trong source tài liệu khác.

# 4. Nguồn đầu vào và mức độ thẩm quyền

Bộ tài liệu này được khởi tạo từ tài liệu bàn giao kỹ thuật ngày 01/09/2026 và các package snapshot được người dùng cung cấp cùng phiên làm việc. Các nguồn được phân biệt theo vai trò để tránh biến tài liệu bàn giao hoặc code snapshot thành source owner ngoài ý muốn.

| **Nguồn** | **Vai trò** | **Được phép dùng để** | **Không được dùng để** |
| --- | --- | --- | --- |
| AZnet Theme Code Reuse Handoff from ConvertFlow v1.0 | Technical handoff - không phải source owner | Xác lập ranh giới ownership, workflow extraction, acceptance gates | Ghi đè source owner của ConvertFlow/RootProfile/WooCommerce |
| ConvertFlow Core P5.223 snapshot | Evidence / provenance candidate | Đối chiếu implementation đã tồn tại khi lập inventory | Mặc nhiên chuyển domain logic sang theme |
| ConvertFlow Pro alpha.118 / P5.162 r1 snapshot | Evidence / provenance candidate | Đối chiếu compatibility/presentation implementation | Suy ra toàn bộ contract công khai nếu chưa kiểm chứng |
| RootProfile alpha.99 snapshot | Evidence / provenance candidate | Đối chiếu profile presentation/integration candidate | Sao chép identity storage hoặc authoritative semantics |

# 5. Change control

| **Loại thay đổi** | **Yêu cầu tối thiểu trước khi merge/ban hành** |
| --- | --- |
| Presentation nội bộ, không đổi contract | Regression visual + responsive + accessibility; cập nhật provenance nếu code được move/port. |
| Đổi public provider contract | Version contract; compatibility path; test provider old/new; cập nhật tài liệu 02 và Decision Log. |
| Đổi ownership | Phải có quyết định kiến trúc rõ, migration plan, rollback và cập nhật toàn bộ tài liệu bị ảnh hưởng. |
| Retire code cũ | Chỉ sau destination + fallback/compatibility + regression QA PASS. |
| Thêm dependency bắt buộc | Mặc định không chấp nhận nếu làm theme hoặc plugin thành hard dependency trái charter; cần quyết định riêng. |
| **Thay đổi Hiến pháp sản phẩm** | Phải có phê duyệt rõ của product owner; cập nhật AZT-05 và mọi source bị ảnh hưởng trước production code/merge; ghi provenance và rollback của quyết định. |

# 6. Nguyên tắc cập nhật tài liệu theo source owner

Nguyên tắc mặc định: một thay đổi chỉ cập nhật tài liệu sở hữu trực tiếp nội dung đã thay đổi. Không đồng bộ nhiều tài liệu chỉ để lặp lại cùng một trạng thái hiện hành.

| **Loại nội dung / thay đổi** | **Owner mặc định** | **Quy tắc cập nhật** |
| --- | --- | --- |
| Governance, source priority, change control, quy tắc tài liệu | AZT-00 | Chỉ cập nhật khi cơ chế quản trị nguồn hoặc cách kiểm soát thay đổi thực sự đổi. |
| Product scope, ownership, non-goals, surface ownership | AZT-01 | Không dùng để ghi tiến độ implementation. |
| Architecture, dependency policy, public integration/provider contract | AZT-02 | Chỉ cập nhật khi boundary/contract/kiến trúc thay đổi; implementation evidence không tự làm đổi kiến trúc. |
| Canonical baseline, artifact registry, code provenance | AZT-03 | Cập nhật khi canonical baseline/provenance thực sự đổi; không bump chỉ vì một branch hoặc test run PASS. |
| Roadmap, milestone/gate, Decision Log, open question | AZT-04 | Cập nhật khi roadmap/gate/quyết định thay đổi; không chép lại mọi evidence chi tiết. |
| Execution order, slice/micro-slice structure dẫn xuất | Implementation Slice Map | Chỉ cập nhật khi cấu trúc/thứ tự thực thi thay đổi đáng kể; không dùng như bản sao current-state. |
| Test run, branch/commit, SHA, local checkpoint, debugging evidence | GitHub evidence/checkpoint | Lưu ở evidence phù hợp; tự nó không tạo nghĩa vụ tăng phiên bản source. |
| Bất biến sản phẩm, product independence, quality floor, release constitution, amendment | **AZT-05** | Chỉ đổi khi product owner phê duyệt. AZT-01/02/04 bị ảnh hưởng phải cập nhật trước implementation; current-state evidence không tự sửa Hiến pháp. |

- Trước khi sửa tài liệu phải xác định rõ: fact/decision này thuộc owner document nào. Nếu xác định được một owner, chỉ sửa owner đó.
- Chỉ cập nhật nhiều tài liệu khi một thay đổi duy nhất thực sự làm thay đổi nhiều phạm vi ownership; khi đó phải nêu rõ từng tài liệu bị ảnh hưởng và lý do.
- Không copy cùng một current-state fact vào nhiều source document để “đồng bộ”. Ưu tiên dẫn chiếu sang source/evidence owner thay vì nhân bản nội dung.
- Evidence không đồng nghĩa source change. Test PASS, branch/commit mới, SHA/package hay một checkpoint cục bộ được ghi ở GitHub evidence/checkpoint; chỉ cập nhật source khi nội dung mà source đó sở hữu đã đổi.
- Nếu chưa có tài liệu phù hợp, tạo tài liệu chuyên trách có phạm vi rõ và đăng ký nó trong AZT-00; không ép nội dung vào một tài liệu không sở hữu chủ đề đó.
- Historical evidence không bị rewrite chỉ để khớp current state. Source document mới supersede bản cũ khi chính nội dung của source owner thay đổi.

# 7. Quy ước phiên bản tài liệu

- Git history là provenance/version history của source; số phiên bản human-readable trong từng tài liệu biểu thị semantic source version.
- v0.x: working source còn open questions quan trọng; v1.0: baseline đủ để dùng làm source ổn định cho implementation.
- Mỗi lần thay đổi có ý nghĩa kiến trúc phải tăng phiên bản và ghi Decision Log.
- Không sửa âm thầm lịch sử để làm mất dấu quyết định cũ; commit/source mới supersede bản cũ.
- Gate nguồn v1.0: hoàn thành file-level provenance inventory của các snapshot nguồn, chốt architecture mode, support matrix WordPress/PHP, namespace/prefix và public integration contract tối thiểu.

# 8. Hiến pháp và code ordering

- Mọi thay đổi có thể làm đổi bản chất sản phẩm, product independence hoặc release constitution phải được quyết định trong AZT-05 trước production code.
- Repo/GitHub evidence có thể chứng minh implementation nhưng không được tự thay AZT-05 hoặc topic owner source.

# 9. GitHub là medium nguồn canonical

Từ v0.4, source nội bộ của AZnet Theme được duy trì dưới dạng Markdown reviewable trong `docs/source/` của canonical repository `truongdinhnamaz/aznet-theme`. Git history là provenance/version history của source; số phiên bản human-readable trong từng tài liệu tiếp tục dùng để biểu thị semantic source version.

- File Markdown tương ứng trên canonical `main` là bản authoritative của source AZnet Theme.
- DOCX/PDF chỉ là derived export cho đọc, lưu trữ hoặc handoff; export không được âm thầm ghi đè source Markdown trên `main`.
- Source change phải đi qua branch/PR, đúng owner document và approval gate tương ứng trước khi merge.
- Implementation evidence vẫn sống ở code history, PR, Actions và `docs/evidence/`; việc cùng nằm trong GitHub không làm evidence trở thành source truth.
- Khi source rule không đổi, tiếp tục implementation và ghi evidence; không bump source chỉ để đồng bộ tiến độ.
- Khi source rule thay đổi, sửa source Markdown trước production code chịu sự chi phối của rule mới.

Canonical paths được đăng ký trong `docs/source/README.md`.
