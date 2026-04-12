<?php include __DIR__ . "/../includes/header.php"; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div id="search-results">
            <?php include __DIR__ . "/partials/search_results.php"; ?>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h3 class="h6">Bá»™ lá»c tÃ¬m kiáº¿m</h3>
                <form method="get" action="search.php" class="mt-3">
                    <div class="mb-3">
                        <label class="form-label small" for="search-q">Tá»« khÃ³a</label>
                        <input class="form-control form-control-sm" id="search-q" type="search" name="q" placeholder="Nháº­p tá»« khÃ³a..." value="<?php echo htmlspecialchars($keyword, ENT_QUOTES, "UTF-8"); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="search-category">Danh má»¥c</label>
                        <select class="form-select form-select-sm" id="search-category" name="category_id">
                            <option value="0">Táº¥t cáº£</option>
                            <?php foreach ($categories as $category) { ?>
                                <option value="<?php echo (int)$category["category_id"]; ?>" <?php echo $categoryId === (int)$category["category_id"] ? "selected" : ""; ?>>
                                    <?php echo htmlspecialchars($category["category_name"], ENT_QUOTES, "UTF-8"); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="search-range">Thá»i gian</label>
                        <select class="form-select form-select-sm" id="search-range" name="range">
                            <option value="all" <?php echo $range === "all" ? "selected" : ""; ?>>Táº¥t cáº£</option>
                            <option value="7d" <?php echo $range === "7d" ? "selected" : ""; ?>>7 ngÃ y gáº§n Ä‘Ã¢y</option>
                            <option value="30d" <?php echo $range === "30d" ? "selected" : ""; ?>>30 ngÃ y gáº§n Ä‘Ã¢y</option>
                            <option value="365d" <?php echo $range === "365d" ? "selected" : ""; ?>>1 nÄƒm gáº§n Ä‘Ã¢y</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small" for="search-sort">Sáº¯p xáº¿p</label>
                        <select class="form-select form-select-sm" id="search-sort" name="sort">
                            <option value="relevance" <?php echo $sort === "relevance" ? "selected" : ""; ?>>LiÃªn quan nháº¥t</option>
                            <option value="newest" <?php echo $sort === "newest" ? "selected" : ""; ?>>Má»›i nháº¥t</option>
                            <option value="oldest" <?php echo $sort === "oldest" ? "selected" : ""; ?>>CÅ© nháº¥t</option>
                        </select>
                    </div>
                    <div class="d-grid">
                        <button class="btn btn-danger btn-sm" type="submit">Ãp dá»¥ng</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var form = document.querySelector('form[action="search.php"]');
        var results = document.getElementById('search-results');
        if (!form || !results) {
            return;
        }

        var setLoading = function () {
            results.innerHTML = '<div class="text-muted small">Äang táº£i káº¿t quáº£...</div>';
        };

        var fetchResults = function (queryString, pushState) {
            if (typeof pushState === "undefined") {
                pushState = true;
            }

            setLoading();
            fetch('api/search.php' + queryString)
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (!data || !data.success) {
                        results.innerHTML = '';
                        if (window.appToast) {
                            window.appToast.show('Không thể tải kết quả.', 'error');
                        }
                        return;
                    }
                    results.innerHTML = data.html || '';
                    if (pushState) {
                        history.pushState(null, '', 'search.php' + queryString);
                    }
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                })
                .catch(function () {
                    results.innerHTML = '';
                    if (window.appToast) {
                        window.appToast.show('Không thể kết nối máy chủ.', 'error');
                    }
                });
        };

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var params = new URLSearchParams(new FormData(form));
            params.set('page', '1');
            fetchResults('?' + params.toString());
        });

        results.addEventListener('click', function (event) {
            var link = event.target.closest('a.page-link');
            if (!link) {
                return;
            }
            event.preventDefault();
            var url = new URL(link.getAttribute('href'), window.location.origin);
            fetchResults(url.search);
        });

        window.addEventListener('popstate', function () {
            fetchResults(window.location.search || '?', false);
        });
    })();
</script>

<?php include __DIR__ . "/../includes/footer.php"; ?>
