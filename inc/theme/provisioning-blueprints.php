<?php
/** Immutable Law 01 provisioning blueprint definitions. */
namespace AZnet\Theme;

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** @return array<int,string> */
function provisioning_blueprint_keys(): array {
    return [ 'law01-v1', 'law01-v1-1' ];
}

/** @return array<string,mixed>|null */
function provisioning_blueprint( string $key ): ?array {
    if ( ! in_array( $key, provisioning_blueprint_keys(), true ) ) { return null; }

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

    $blueprint = [
        'key' => $key,
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

    if ( 'law01-v1-1' !== $key ) { return $blueprint; }

    $starter_content = static function ( string $context, string $documents, string $focus, string $questions ): string {
        return '<p>' . $context . ' Bài viết mẫu này giúp người quản trị hình dung một nội dung kiến thức hoàn chỉnh trên website. Nội dung tập trung vào việc chuẩn bị và tổ chức thông tin trước khi trao đổi với người có chuyên môn; không thay thế việc đánh giá hồ sơ thực tế và không đưa ra kết luận cho một tình huống cụ thể.</p>'
            . '<h2>Xác định điều cần làm rõ</h2><p>Hãy mô tả ngắn gọn vấn đề, những người hoặc tổ chức có liên quan và kết quả mong muốn ở thời điểm hiện tại. Nên tách điều mình trực tiếp biết, điều có tài liệu chứng minh và điều mới chỉ nghe kể. Không cần cố dùng thuật ngữ chuyên môn khi chưa chắc về ý nghĩa. Một bản tóm tắt rõ dữ kiện thường giúp buổi trao đổi đi nhanh hơn và giảm nguy cơ biến giả định thành thông tin đã được xác nhận.</p>'
            . '<h2>Nhóm tài liệu nên chuẩn bị</h2><p>' . $documents . ' Nên giữ bản gốc an toàn và tạo một bộ bản sao hoặc tệp số để đối chiếu. Đặt tên tệp theo nội dung và thời điểm để có thể tìm lại nhanh. Nếu có nhiều phiên bản của cùng một giấy tờ, nên giữ các phiên bản liên quan và ghi chú sự khác nhau. Phần cần giải thích nên ghi ở một ghi chú riêng, không chỉnh sửa nội dung tài liệu gốc chỉ để làm câu chuyện dễ đọc hơn.</p>'
            . '<h2>Sắp xếp theo dòng sự việc</h2><p>Một bảng thời gian đơn giản có thể gồm thời điểm, sự kiện, người tham gia, tài liệu liên quan và điều còn chưa chắc chắn. Không nhớ chính xác mốc nào thì nên đánh dấu để kiểm tra sau thay vì tự điền. ' . $focus . ' Khi hai nguồn thể hiện khác nhau, hãy ghi nhận sự khác biệt và giữ cả hai để người rà soát biết điểm nào cần xác minh thêm.</p>'
            . '<h2>Chuẩn bị câu hỏi cho buổi trao đổi</h2><p>' . $questions . ' Nên ưu tiên các câu hỏi gắn với mục tiêu thực tế: điều gì cần làm rõ trước, tài liệu nào còn thiếu, ai đang giữ tài liệu đó và bước làm việc nào đã diễn ra. Nếu có nhiều vấn đề, hãy chia thành từng nhóm. Danh sách câu hỏi cụ thể giúp người tiếp nhận xác định phạm vi trao đổi và đề nghị bổ sung thông tin cần thiết.</p>'
            . '<h2>Kiểm tra lại trước khi gửi hồ sơ</h2><p>Hãy chắc rằng tệp quan trọng có thể mở, ảnh chụp đủ rõ và nội dung trao đổi điện tử còn ngữ cảnh cần thiết. Có thể chuẩn bị một bản tóm tắt ngắn cùng bộ tài liệu chi tiết để người tiếp nhận đi từ tổng quan đến chứng cứ. Với thông tin nhạy cảm, nên hỏi trước về kênh gửi phù hợp. Sau buổi làm việc, ghi lại tài liệu cần bổ sung và câu hỏi còn mở để lần trao đổi sau có điểm bắt đầu rõ ràng.</p>'
            . '<p><em>Đây là starter copy phục vụ giai đoạn dựng website. Người quản trị cần rà soát và thay thế bằng nội dung phù hợp với phạm vi hoạt động thực tế trước khi coi đây là nội dung chính thức.</em></p>';
    };

    $article = static fn( string $title, string $excerpt, string $category_role, string $media_role, string $context, string $documents, string $focus, string $questions ): array => [
        'title' => $title,
        'excerpt' => $excerpt,
        'content' => $starter_content( $context, $documents, $focus, $questions ),
        'category_role' => $category_role,
        'media_role' => $media_role,
    ];

    $blueprint['editorial_examples'] = [
        'status' => 'draft',
        'items' => [
            'knowledge_business' => $article(
                'Những tài liệu doanh nghiệp nên chuẩn bị trước khi trao đổi pháp lý',
                'Gợi ý cách tập hợp và sắp xếp hồ sơ doanh nghiệp để buổi trao đổi đi thẳng vào vấn đề cần làm rõ.',
                'knowledge_business', 'editorial-1',
                'Với vấn đề phát sinh trong hoạt động doanh nghiệp, thông tin thường nằm rải rác giữa hồ sơ pháp lý, quyết định nội bộ, hợp đồng, thư từ và tài liệu vận hành.',
                'Có thể bắt đầu từ giấy tờ đăng ký, điều lệ hoặc quy chế liên quan, hợp đồng và phụ lục, biên bản hoặc quyết định nội bộ, chứng từ giao dịch, thư điện tử và các văn bản đã gửi hoặc nhận.',
                'Đặc biệt nên đánh dấu mốc ký kết, mốc thực hiện công việc, thay đổi người phụ trách, lần phát sinh bất đồng và những lần các bên đã trao đổi để xử lý.',
                'Các câu hỏi nên xoay quanh mục tiêu kinh doanh cần bảo vệ, nghĩa vụ đang được hiểu khác nhau, tài liệu nào phản ánh thỏa thuận gần nhất và quyết định nội bộ nào có liên quan.'
            ),
            'knowledge_civil' => $article(
                'Những tài liệu nên chuẩn bị khi phát sinh tranh chấp dân sự',
                'Một cách đơn giản để gom hợp đồng, chứng từ và trao đổi thành bộ hồ sơ dễ rà soát.',
                'knowledge_civil', 'editorial-2',
                'Tranh chấp dân sự thường khó theo dõi khi mỗi bên nhớ sự việc theo một cách và tài liệu được lưu ở nhiều nơi khác nhau.',
                'Nên gom hợp đồng, giấy nhận tiền hoặc chứng từ thanh toán, biên bản bàn giao, tin nhắn hoặc thư điện tử, hình ảnh liên quan và các văn bản mà các bên đã trao đổi trong quá trình giải quyết.',
                'Nên tách rõ thời điểm hình thành thỏa thuận, thời điểm thực hiện, thời điểm có dấu hiệu không thống nhất và những đề nghị xử lý đã được đưa ra.',
                'Hãy ghi lại điều mình yêu cầu bên kia thực hiện, điều bên kia đã phản hồi, phần nào có văn bản xác nhận và phần nào mới chỉ được trao đổi bằng lời.'
            ),
            'knowledge_criminal' => $article(
                'Những thông tin nên ghi nhận khi cần hỗ trợ pháp lý trong vụ việc hình sự',
                'Gợi ý chuẩn bị thông tin, giấy tờ và dòng sự việc để người tiếp nhận có bức tranh ban đầu rõ ràng hơn.',
                'knowledge_criminal', 'editorial-3',
                'Khi một vụ việc có dấu hiệu liên quan đến quy trình tố tụng hoặc cơ quan có thẩm quyền, việc ghi nhận chính xác nguồn tài liệu và mốc làm việc đặc biệt quan trọng.',
                'Nên giữ các giấy mời, giấy triệu tập, biên bản, quyết định hoặc văn bản đã nhận; tài liệu cá nhân có liên quan; thông tin về những lần làm việc; cùng bản sao các tài liệu đã cung cấp nếu có.',
                'Nên ghi đúng ngày giờ của từng lần liên hệ hoặc làm việc, người tham gia, tài liệu đã giao nhận và nội dung mình còn chưa hiểu rõ, thay vì dựa vào trí nhớ sau một thời gian dài.',
                'Có thể chuẩn bị câu hỏi về vai trò hiện tại của mình trong vụ việc, tài liệu nào đã được tiếp nhận, nội dung nào cần giải thích thêm và cách chuẩn bị cho lần làm việc tiếp theo.'
            ),
            'knowledge_real_estate' => $article(
                'Hồ sơ cơ bản cần rà soát khi làm việc với vấn đề đất đai',
                'Cách tập hợp giấy tờ về quyền, hiện trạng, giao dịch và quá trình sử dụng để việc rà soát có điểm bắt đầu rõ ràng.',
                'knowledge_real_estate', 'editorial-4',
                'Vấn đề đất đai thường liên quan đồng thời đến giấy tờ, quá trình sử dụng, hiện trạng thực tế, giao dịch và thông tin từ nhiều thời kỳ.',
                'Nên tập hợp giấy tờ về quyền sử dụng hoặc nguồn gốc, hợp đồng hay giấy viết tay liên quan, hồ sơ đo đạc hoặc sơ đồ nếu có, chứng từ tài chính, biên bản làm việc và hình ảnh thể hiện hiện trạng mà mình đang có.',
                'Hãy lập dòng thời gian về việc nhận chuyển giao hoặc bắt đầu sử dụng, các lần thay đổi hiện trạng, giao dịch, điều chỉnh giấy tờ và những lần phát sinh bất đồng hoặc làm việc với cơ quan hay bên liên quan.',
                'Các câu hỏi hữu ích gồm giấy tờ nào đang có bản gốc, thông tin nào giữa giấy tờ và hiện trạng chưa khớp, ai đang quản lý hoặc sử dụng thực tế và nội dung nào cần được xác minh thêm.'
            ),
            'knowledge_family' => $article(
                'Thông tin nên chuẩn bị trước khi trao đổi về vấn đề hôn nhân và gia đình',
                'Gợi ý tổ chức giấy tờ nhân thân, thông tin gia đình, tài sản và các trao đổi liên quan theo cách dễ rà soát.',
                'knowledge_family', 'editorial-2',
                'Vấn đề hôn nhân và gia đình thường có cả dữ kiện pháp lý lẫn yếu tố đời sống, vì vậy việc trình bày rõ điều cần giải quyết giúp tránh sa vào những chi tiết không liên quan đến mục tiêu chính.',
                'Có thể chuẩn bị giấy tờ nhân thân và quan hệ gia đình, thông tin về con nếu có, tài liệu về tài sản hoặc nghĩa vụ tài chính có liên quan, các thỏa thuận đã lập và những trao đổi quan trọng cần người tiếp nhận biết.',
                'Nên ghi các mốc thay đổi đáng chú ý trong đời sống hoặc quản lý tài sản, các lần hai bên đã trao đổi về phương án và những vấn đề hiện vẫn chưa thống nhất.',
                'Hãy tách rõ điều cần ưu tiên giải quyết, thông tin nào hai bên cùng xác nhận, thông tin nào còn khác nhau và tài liệu nào có thể giúp làm rõ từng điểm.'
            ),
            'knowledge_labor' => $article(
                'Hồ sơ thường cần rà soát khi phát sinh vấn đề trong quan hệ lao động',
                'Một danh sách chuẩn bị để người lao động hoặc doanh nghiệp có thể hệ thống tài liệu trước buổi trao đổi.',
                'knowledge_labor', 'editorial-1',
                'Quan hệ lao động thường được phản ánh qua nhiều lớp tài liệu từ tuyển dụng, hợp đồng, quá trình làm việc đến quyết định và trao đổi khi phát sinh vấn đề.',
                'Nên gom hợp đồng và phụ lục, mô tả công việc nếu có, bảng lương hoặc chứng từ liên quan, quyết định hoặc thông báo, biên bản làm việc, nội quy hoặc chính sách đã được cung cấp và các trao đổi điện tử có liên quan.',
                'Dòng thời gian nên thể hiện thời điểm bắt đầu công việc, thay đổi vị trí hoặc điều kiện làm việc, các lần đánh giá hoặc trao đổi, sự kiện làm phát sinh vấn đề và những bước hai bên đã thực hiện sau đó.',
                'Nên chuẩn bị câu hỏi về tài liệu nào đang điều chỉnh công việc thực tế, nội dung nào chưa thống nhất, thông báo nào đã được gửi nhận và mục tiêu giải quyết của mỗi bên ở thời điểm hiện tại.'
            ),
            'case_analysis' => $article(
                'Cách hệ thống hóa tài liệu trước khi phân tích một vụ việc pháp lý',
                'Phương pháp tổ chức hồ sơ theo dữ kiện, tài liệu và câu hỏi mở để việc phân tích có nền tảng rõ ràng.',
                'case_analysis', 'editorial-3',
                'Một vụ việc trở nên khó phân tích khi dữ kiện, cảm nhận, giả định và tài liệu bị trộn lẫn trong cùng một câu chuyện dài.',
                'Hãy tạo một danh mục tài liệu gồm tên tài liệu, nguồn nhận, thời điểm, bản gốc hay bản sao và vấn đề mà tài liệu có thể giúp làm rõ. Các bản ghi âm, ảnh, tin nhắn hoặc thư điện tử nên được giữ cùng ngữ cảnh thay vì chỉ trích một đoạn rời rạc.',
                'Sau khi có dòng thời gian, có thể tạo ba cột riêng: dữ kiện đã có tài liệu, dữ kiện do một bên trình bày nhưng chưa kiểm tra và câu hỏi còn bỏ ngỏ. Cách này giúp tránh biến giả định thành sự thật trong quá trình đọc hồ sơ.',
                'Các câu hỏi phân tích nên bắt đầu từ mục tiêu, các điểm hai bên thống nhất, các điểm đang mâu thuẫn, nguồn chứng cứ tương ứng và những thông tin còn thiếu trước khi cân nhắc phương án tiếp theo.'
            ),
            'legal_news' => $article(
                'Cách theo dõi và kiểm tra thông tin pháp luật trước khi sử dụng',
                'Gợi ý quy trình biên tập thận trọng khi website muốn chia sẻ thông tin pháp luật từ các nguồn đang được theo dõi.',
                'legal_news', 'editorial-4',
                'Khi biên tập nội dung thuộc nhóm tin pháp luật, điều quan trọng là phân biệt thông tin mới đọc được với thông tin đã được kiểm tra từ nguồn phù hợp.',
                'Nên lưu đường dẫn hoặc bản ghi nguồn, tiêu đề gốc, đơn vị công bố, thời điểm truy cập, tài liệu gốc đi kèm nếu có và ghi chú phần nào đã được kiểm tra. Không nên chỉ dựa vào ảnh chụp màn hình hoặc một bài đăng được chia sẻ lại mà mất dấu nguồn ban đầu.',
                'Có thể tạo dòng theo dõi gồm lúc phát hiện thông tin, lúc kiểm tra nguồn, nội dung cần đối chiếu và trạng thái biên tập. Nếu một điểm chưa xác minh được, hãy để ở trạng thái chờ thay vì viết theo cách khiến người đọc tưởng đó là dữ kiện đã chắc chắn.',
                'Trước khi đăng, nên hỏi nguồn này có trực tiếp hay chỉ dẫn lại, ngữ cảnh có đầy đủ không, nội dung có thể thay đổi theo thời điểm không và bài viết đã nói rõ phạm vi thông tin mà mình thực sự kiểm tra hay chưa.'
            ),
        ],
    ];

    return $blueprint;
}
