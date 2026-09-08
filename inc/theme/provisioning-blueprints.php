<?php
/** Immutable Law 01 provisioning blueprint definitions. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<int,string> */
function provisioning_blueprint_keys(): array {
    return [ 'law01-v1' ];
}

/** @return array<string,mixed>|null */
function provisioning_blueprint( string $key ): ?array {
    if ( 'law01-v1' !== $key ) { return null; }

    $page = static fn( string $title, string $excerpt, string $content, ?string $parent = null ): array => [
        'title' => $title,
        'status' => 'publish',
        'excerpt' => $excerpt,
        'content' => $content,
        'parent_role' => $parent,
    ];
    $category = static fn( string $name, ?string $parent = null ): array => [
        'name' => $name,
        'parent_role' => $parent,
    ];

    return [
        'key' => 'law01-v1',
        'homepage_preset' => 'law-01',
        'pages' => [
            'home' => $page(
                'Giải pháp pháp lý rõ ràng cho cá nhân và doanh nghiệp',
                'Chúng tôi hỗ trợ khách hàng tiếp cận vấn đề pháp lý theo hướng rõ ràng, thực tiễn và phù hợp với từng hoàn cảnh cụ thể.',
                '<p>Nội dung giới thiệu tổng quan có thể được chỉnh sửa trực tiếp trong WordPress.</p>'
            ),
            'about' => $page(
                'Giới thiệu',
                'Giới thiệu định hướng hoạt động, nguyên tắc làm việc và cách tiếp cận khách hàng.',
                '<p>Hãy thay nội dung này bằng thông tin thực tế về tổ chức, đội ngũ và định hướng phục vụ của bạn.</p><h2>Nguyên tắc làm việc</h2><p>Tiếp cận rõ ràng, tôn trọng hồ sơ thực tế và trao đổi minh bạch về phạm vi công việc.</p>'
            ),
            'services' => $page(
                'Dịch vụ pháp lý',
                'Tổng quan các nhóm dịch vụ pháp lý dành cho cá nhân và doanh nghiệp.',
                '<p>Chọn nhóm dịch vụ phù hợp để xem phạm vi hỗ trợ và chuẩn bị thông tin trước khi liên hệ.</p>'
            ),
            'service_business' => $page('Luật Doanh nghiệp','Hỗ trợ rà soát và xử lý các vấn đề pháp lý thường gặp trong hoạt động doanh nghiệp.','<h2>Phạm vi hỗ trợ</h2><p>Trao đổi nhu cầu, rà soát hồ sơ và xác định bước xử lý phù hợp.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị hồ sơ doanh nghiệp và tài liệu liên quan đến vấn đề cần trao đổi.</p>','services'),
            'service_civil' => $page('Dân sự & Tranh chấp','Hỗ trợ khách hàng nhận diện vấn đề, rà soát hồ sơ và xác định hướng xử lý đối với tranh chấp dân sự.','<h2>Phạm vi hỗ trợ</h2><p>Rà soát thông tin, tài liệu và các lựa chọn xử lý phù hợp với tình huống cụ thể.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị hợp đồng, giấy tờ, trao đổi và tài liệu liên quan.</p>','services'),
            'service_criminal' => $page('Hình sự','Hỗ trợ tiếp nhận hồ sơ và trao đổi về nhu cầu pháp lý trong các vụ việc hình sự.','<h2>Phạm vi hỗ trợ</h2><p>Tiếp nhận thông tin, rà soát tài liệu và trao đổi về bước làm việc tiếp theo.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị tài liệu, quyết định, giấy mời hoặc giấy tờ liên quan nếu có.</p>','services'),
            'service_real_estate' => $page('Đất đai & Bất động sản','Hỗ trợ rà soát hồ sơ và vấn đề pháp lý liên quan đến đất đai, nhà ở và giao dịch bất động sản.','<h2>Phạm vi hỗ trợ</h2><p>Rà soát hồ sơ và xác định nội dung cần làm rõ trước khi lựa chọn hướng xử lý.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị giấy tờ về quyền sử dụng đất, giao dịch và tài liệu liên quan.</p>','services'),
            'service_family' => $page('Hôn nhân & Gia đình','Hỗ trợ tiếp nhận và rà soát các vấn đề pháp lý trong quan hệ hôn nhân và gia đình.','<h2>Phạm vi hỗ trợ</h2><p>Trao đổi nhu cầu và tài liệu để xác định phạm vi công việc phù hợp.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị giấy tờ nhân thân, tài sản và các tài liệu liên quan nếu có.</p>','services'),
            'service_labor' => $page('Lao động','Hỗ trợ người lao động và doanh nghiệp rà soát các vấn đề pháp lý phát sinh trong quan hệ lao động.','<h2>Phạm vi hỗ trợ</h2><p>Rà soát hồ sơ và trao đổi về phương án làm việc phù hợp với từng tình huống.</p><h2>Thông tin nên chuẩn bị</h2><p>Chuẩn bị hợp đồng, quyết định, bảng lương và tài liệu liên quan nếu có.</p>','services'),
            'team' => $page('Đội ngũ luật sư','Giới thiệu định hướng chuyên môn và cách tổ chức đội ngũ.','<p>Hãy bổ sung thông tin đội ngũ thực tế từ nguồn hồ sơ chính thức của website. AZnet Theme không tạo người hoặc thành tích mẫu.</p>'),
            'process' => $page('Quy trình tư vấn','Quy trình starter gồm bốn bước có thể chỉnh sửa theo cách vận hành thực tế.','<ol><li><strong>Tiếp nhận yêu cầu</strong><p>Ghi nhận nhu cầu và tài liệu ban đầu.</p></li><li><strong>Đánh giá vấn đề</strong><p>Rà soát thông tin để xác định phạm vi cần làm rõ.</p></li><li><strong>Đề xuất phương án</strong><p>Trao đổi hướng xử lý và phạm vi công việc phù hợp.</p></li><li><strong>Đồng hành thực hiện</strong><p>Phối hợp theo phạm vi đã được thống nhất.</p></li></ol>'),
            'faq' => $page('Câu hỏi thường gặp','Một số câu hỏi chung về quá trình tiếp nhận yêu cầu tư vấn.','<details><summary>Tôi cần chuẩn bị gì trước khi tư vấn?</summary><p>Hãy chuẩn bị các giấy tờ, hợp đồng, quyết định và thông tin liên quan đến vấn đề cần trao đổi.</p></details><details><summary>Quy trình tiếp nhận yêu cầu diễn ra thế nào?</summary><p>Yêu cầu được tiếp nhận, rà soát thông tin ban đầu và xác định phạm vi trao đổi tiếp theo.</p></details><details><summary>Chi phí tư vấn được xác định ra sao?</summary><p>Chi phí cần được trao đổi dựa trên phạm vi công việc thực tế trước khi thực hiện.</p></details><details><summary>Có thể gửi hồ sơ trước khi đặt lịch không?</summary><p>Bạn có thể trao đổi với đơn vị cung cấp dịch vụ về cách gửi tài liệu phù hợp trước buổi làm việc.</p></details><details><summary>Tôi nên cung cấp những thông tin nào khi liên hệ?</summary><p>Hãy mô tả ngắn gọn vấn đề, mục tiêu cần hỗ trợ và những tài liệu đang có.</p></details>'),
            'contact' => $page('Liên hệ','Trang liên hệ để bổ sung thông tin và phương thức tiếp nhận yêu cầu thực tế.','<p>Hãy cập nhật thông tin liên hệ thực tế của tổ chức trước khi đưa website vào vận hành chính thức.</p>'),
        ],
        'categories' => [
            'knowledge' => $category('Kiến thức pháp luật'),
            'knowledge_business' => $category('Doanh nghiệp','knowledge'),
            'knowledge_civil' => $category('Dân sự','knowledge'),
            'knowledge_criminal' => $category('Hình sự','knowledge'),
            'knowledge_real_estate' => $category('Đất đai','knowledge'),
            'knowledge_family' => $category('Hôn nhân & Gia đình','knowledge'),
            'knowledge_labor' => $category('Lao động','knowledge'),
            'case_analysis' => $category('Phân tích vụ việc'),
            'legal_news' => $category('Tin pháp luật'),
        ],
        'menu' => [
            'location' => 'primary',
            'label' => 'AZnet Primary',
            'roles' => [ 'home','about','services','team','contact' ],
            'labels' => [ 'home' => 'Trang chủ', 'about' => 'Giới thiệu', 'services' => 'Dịch vụ pháp lý', 'team' => 'Đội ngũ luật sư', 'contact' => 'Liên hệ' ],
        ],
        'editorial_examples' => [
            'status' => 'draft',
            'items' => [],
        ],
    ];
}
