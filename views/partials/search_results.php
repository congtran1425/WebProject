<?php if (!isset($keyword)) { $keyword = ""; } ?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
    <div>
        <h2 class="h6 mb-1 text-uppercase">Kết quả tìm kiếm</h2>
        <?php if ($keyword !== "") { ?>
            <div class="text-muted small">Từ khóa: "<?php echo htmlspecialchars($keyword, ENT_QUOTES, "UTF-8"); ?>" - <?php echo (int)$total; ?> kết quả</div>
        <?php } elseif ($shouldSearch) { ?>
            <div class="text-muted small"><?php echo (int)$total; ?> kết quả</div>
        <?php } else { ?>
            <div class="text-muted small">Nhập từ khóa hoặc chọn bộ lọc để bắt đầu.</div>
        <?php } ?>
    </div>
</div>

<?php if ($validationError) { ?>
    <div class="alert alert-warning" role="alert">
        <?php echo htmlspecialchars($validationError, ENT_QUOTES, "UTF-8"); ?>
    </div>
<?php } elseif ($shouldSearch && $total === 0) { ?>
    <div class="alert alert-light border" role="alert">
        Không tìm thấy kết quả phù hợp. Hãy thử từ khóa khác hoặc thay đổi bộ lọc.
    </div>
<?php } ?>

<?php if ($shouldSearch && $articles && $total > 0) { ?>
    <div class="row g-2">
        <?php while ($row = $articles->fetch_assoc()) { ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card compact-card border-0 shadow-sm h-100 position-relative">
                    <div class="card-body">
                        <div class="thumb-frame compact mb-2">
                            <?php if (!empty($row["thumbnail"])) { ?>
                                <img class="article-thumb" src="<?php echo htmlspecialchars($row["thumbnail"], ENT_QUOTES, "UTF-8"); ?>" alt="Thumbnail">
                            <?php } else { ?>
                                <div class="thumb-placeholder">Chua c� ?nh</div>
                            <?php } ?>
                        </div>
                        <h3 class="h6 mb-1">
                            <a class="text-decoration-none text-dark stretched-link" href="article_detail.php?id=<?php echo (int)$row["article_id"]; ?>">
                                <?php echo htmlspecialchars($row["title"], ENT_QUOTES, "UTF-8"); ?>
                            </a>
                        </h3>
                        <p class="text-muted small mb-2 clamp-2">
                            <?php echo htmlspecialchars($row["summary"], ENT_QUOTES, "UTF-8"); ?>
                        </p>
                        <?php if (!empty($row["category_name"])) { ?>
                            <span class="badge bg-light text-muted"><?php echo htmlspecialchars($row["category_name"], ENT_QUOTES, "UTF-8"); ?></span>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if ($totalPages > 1) { ?>
        <nav class="mt-3" aria-label="Search pagination">
            <ul class="pagination pagination-sm justify-content-center">
                <?php
                $queryBase = [
                    "q" => $keyword,
                    "category_id" => $categoryId,
                    "range" => $range,
                    "sort" => $sort,
                ];
                for ($p = 1; $p <= $totalPages; $p++) {
                    $queryBase["page"] = $p;
                    $url = "search.php?" . http_build_query($queryBase);
                    $active = $p === $page ? "active" : "";
                ?>
                    <li class="page-item <?php echo $active; ?>">
                        <a class="page-link" href="<?php echo htmlspecialchars($url, ENT_QUOTES, "UTF-8"); ?>"><?php echo $p; ?></a>
                    </li>
                <?php } ?>
            </ul>
        </nav>
    <?php } ?>
<?php } ?>