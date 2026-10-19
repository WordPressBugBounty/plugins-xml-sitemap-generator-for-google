"use strict";

jQuery(document).ready(function ($) {
    if (typeof sggBrokenLinks === "undefined") {
        return;
    }

    const $section = $("#broken_links");

    if (!$section.hasClass("active")) {
        return;
    }

    const $scanBtn = $("#sgg-bl-run-scan");
    const $progress = $("#sgg-bl-progress");
    const $progressFill = $("#sgg-bl-progress-fill");
    const $progressText = $("#sgg-bl-progress-text");
    const $lastScan = $("#sgg-bl-last-scan");
    const $tableBody = $("#sgg-bl-table-body");
    const $searchInput = $("#sgg-bl-search");
    const $filterPills = $(".sgg-bl-filter-pill");
    const $summaryCards = $(".sgg-bl-summary-card");
    const $pagination = $("#sgg-bl-pagination");
    const $paginationInfo = $("#sgg-bl-pagination-info");
    const $paginationControls = $("#sgg-bl-pagination-controls");
    const $showIgnored = $("#sgg-bl-show-ignored");
    const $ignoredSection = $("#sgg-bl-ignored");
    const $ignoredList = $("#sgg-bl-ignored-list");

    const perPage = parseInt(sggBrokenLinks.per_page, 10) || 25;
    const strings = sggBrokenLinks.strings;

    let allRows = [];
    let ignoredUrls = [];
    let activeFilter = "all";
    let searchQuery = "";
    let currentPage = 1;
    let isScanning = false;

    function escapeHtml(text) {
        return $("<div>").text(text || "").html();
    }

    function sprintf(template) {
        const args = Array.prototype.slice.call(arguments, 1);
        let index = 0;
        return template.replace(/%(\d+)\$s|%s/g, function (match, position) {
            if (position) {
                return args[parseInt(position, 10) - 1];
            }
            return args[index++];
        });
    }

    function statusBadge(row) {
        let label;

        if (row.status === "broken") {
            label = strings.statusBroken + (row.code ? " " + row.code : "");
            return '<span class="sgg-bl-status-badge broken">' + escapeHtml(label) + "</span>";
        }

        if (row.status === "redirect") {
            label = strings.statusRedirect + (row.code ? " " + row.code : "");
            return '<span class="sgg-bl-status-badge">' + escapeHtml(label) + "</span>";
        }

        return '<span class="sgg-bl-status-badge">' + escapeHtml(strings.statusUnknown) + "</span>";
    }

    function sourcesHtml(row) {
        const sources = row.sources || [];

        if (!sources.length) {
            return "—";
        }

        const first = sources[0];
        let html = '<div class="sgg-bl-sources">';

        if (first.edit_link) {
            html += '<a href="' + escapeHtml(first.edit_link) + '">' + escapeHtml(first.title || first.edit_link) + "</a>";
        } else {
            html += escapeHtml(first.title || "");
        }

        if (sources.length > 1) {
            html += '<span class="sgg-bl-more-sources">' + escapeHtml(sprintf(strings.moreSources, sources.length - 1)) + "</span>";
            html += '<ul class="sgg-bl-extra-sources hidden-area">';
            sources.slice(1).forEach(function (source) {
                if (source.edit_link) {
                    html += '<li><a href="' + escapeHtml(source.edit_link) + '">' + escapeHtml(source.title || source.edit_link) + "</a></li>";
                } else {
                    html += "<li>" + escapeHtml(source.title || "") + "</li>";
                }
            });
            html += "</ul>";
        }

        html += "</div>";

        return html;
    }

    function actionsHtml(row) {
        const sources = row.sources || [];
        let html = '<div class="sgg-bl-row-actions">';

        if (sources.length && sources[0].edit_link) {
            html += '<a href="' + escapeHtml(sources[0].edit_link) + '" class="grim-button white sgg-bl-action-btn"><span>' + escapeHtml(strings.edit) + "</span></a>";
        }

        html += '<button type="button" class="grim-button white sgg-bl-action-btn sgg-bl-recheck" data-url="' + escapeHtml(row.url) + '"><span>' + escapeHtml(strings.recheck) + "</span></button>";
        html += '<button type="button" class="grim-button white sgg-bl-action-btn sgg-bl-ignore" data-url="' + escapeHtml(row.url) + '"><span>' + escapeHtml(strings.ignore) + "</span></button>";
        html += "</div>";

        return html;
    }

    function renderRow(row) {
        return (
            "<tr>" +
            '<td><a href="' + escapeHtml(row.url) + '" class="sgg-bl-url" target="_blank" rel="noopener noreferrer" title="' + escapeHtml(row.url) + '">' + escapeHtml(row.url) + "</a></td>" +
            "<td>" + sourcesHtml(row) + "</td>" +
            "<td>" + statusBadge(row) + "</td>" +
            "<td>" + escapeHtml("image" === row.type ? strings.typeImage : strings.typeLink) + "</td>" +
            "<td>" + actionsHtml(row) + "</td>" +
            "</tr>"
        );
    }

    function filteredRows() {
        return allRows.filter(function (row) {
            if (activeFilter !== "all" && row.status !== activeFilter) {
                return false;
            }

            if (searchQuery) {
                const haystack = (row.url || "").toLowerCase() + " " + (row.sources || []).map(function (source) {
                    return (source.title || "").toLowerCase();
                }).join(" ");

                if (haystack.indexOf(searchQuery) === -1) {
                    return false;
                }
            }

            return true;
        });
    }

    function renderPagination(total) {
        const totalPages = Math.max(1, Math.ceil(total / perPage));

        if (total <= perPage) {
            $pagination.addClass("hidden-area");
            return;
        }

        const from = (currentPage - 1) * perPage + 1;
        const to = Math.min(currentPage * perPage, total);

        $paginationInfo.text(sprintf(strings.showing, from, to, total));

        let html = "";
        html += '<button type="button" class="grim-button white sgg-bl-action-btn sgg-bl-page-prev"' + (currentPage <= 1 ? " disabled" : "") + "><span>" + escapeHtml(strings.prev) + "</span></button>";
        html += '<button type="button" class="grim-button white sgg-bl-action-btn sgg-bl-page-next"' + (currentPage >= totalPages ? " disabled" : "") + "><span>" + escapeHtml(strings.next) + "</span></button>";

        $paginationControls.html(html);
        $pagination.removeClass("hidden-area");
    }

    function renderTable() {
        const rows = filteredRows();
        const totalPages = Math.max(1, Math.ceil(rows.length / perPage));

        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        if (!rows.length) {
            let message = strings.noIssues;

            if (!allRows.length) {
                message = sggBrokenLinks.has_results ? strings.noIssues : strings.noScan;
            } else if (activeFilter !== "all" || searchQuery) {
                message = strings.noMatches;
            }

            $tableBody.html('<tr class="sgg-bl-empty-row"><td colspan="5">' + escapeHtml(message) + "</td></tr>");
            $pagination.addClass("hidden-area");
            return;
        }

        const start = (currentPage - 1) * perPage;
        const pageRows = rows.slice(start, start + perPage);

        $tableBody.html(pageRows.map(renderRow).join(""));
        renderPagination(rows.length);
    }

    function renderIgnored() {
        if (!ignoredUrls.length) {
            $showIgnored.addClass("hidden-area");
            $ignoredSection.addClass("hidden-area");
            return;
        }

        $showIgnored.removeClass("hidden-area");

        let html = "";
        ignoredUrls.forEach(function (url) {
            html += (
                "<li><span>" + escapeHtml(url) + "</span>" +
                '<button type="button" class="grim-button white sgg-bl-action-btn sgg-bl-unignore" data-url="' + escapeHtml(url) + '"><span>' + escapeHtml(strings.unignore) + "</span></button></li>"
            );
        });

        $ignoredList.html(html);
    }

    function updateSummary(summary) {
        summary = summary || {};
        $("#sgg-bl-summary-broken").text(summary.broken || 0);
        $("#sgg-bl-summary-redirect").text(summary.redirect || 0);
        $("#sgg-bl-summary-unknown").text(summary.unknown || 0);
        $("#sgg-bl-summary-checked").text(summary.checked || 0);
    }

    function updateLastScan(scannedAt) {
        if (scannedAt) {
            $lastScan.text(sprintf(strings.lastScan, scannedAt));
        }
    }

    function applyResults(data) {
        allRows = data.rows || [];
        ignoredUrls = data.ignored || [];
        sggBrokenLinks.has_results = !!data.scanned_at;

        updateSummary(data.summary);
        updateLastScan(data.scanned_at);
        renderTable();
        renderIgnored();
    }

    function loadResults() {
        return $.post(sggBrokenLinks.ajax_url, {
            action: "sgg_broken_links_results",
            nonce: sggBrokenLinks.nonce,
        }).done(function (response) {
            if (response.success) {
                applyResults(response.data);
            }
        });
    }

    function updateProgress(data) {
        const phase = data.phase === "extract" ? strings.scanningContent : strings.checking;

        $progress.removeClass("hidden-area");
        $progressFill.css("width", (data.progress || 0) + "%");
        $progressText.text(sprintf(phase, data.processed || 0, data.total || 0));
    }

    function finishScan(failed) {
        isScanning = false;
        $scanBtn.prop("disabled", false).find("span").text(strings.runScan);
        $progress.addClass("hidden-area");
        $progressFill.css("width", "0%");

        if (failed) {
            window.alert(strings.scanFailed);
        }
    }

    function runBatch() {
        $.post(sggBrokenLinks.ajax_url, {
            action: "sgg_broken_links_batch",
            nonce: sggBrokenLinks.nonce,
        }).done(function (response) {
            if (!response.success) {
                finishScan(true);
                return;
            }

            updateProgress(response.data);

            if (response.data.done) {
                loadResults().always(function () {
                    finishScan(false);
                });
                return;
            }

            runBatch();
        }).fail(function () {
            finishScan(true);
        });
    }

    $scanBtn.on("click", function () {
        if (isScanning) {
            return;
        }

        isScanning = true;
        currentPage = 1;
        $scanBtn.prop("disabled", true).find("span").text(strings.scanning);

        $.post(sggBrokenLinks.ajax_url, {
            action: "sgg_broken_links_start",
            nonce: sggBrokenLinks.nonce,
        }).done(function (response) {
            if (!response.success) {
                finishScan(true);
                return;
            }

            updateProgress({ progress: 0, processed: 0, total: response.data.total_posts, phase: "extract" });
            runBatch();
        }).fail(function () {
            finishScan(true);
        });
    });

    function setFilter(filter) {
        activeFilter = filter;
        currentPage = 1;

        $filterPills.removeClass("active").filter('[data-filter="' + filter + '"]').addClass("active");
        $summaryCards.removeClass("active").filter('[data-filter="' + filter + '"]').addClass("active");

        renderTable();
    }

    $filterPills.on("click", function () {
        setFilter($(this).data("filter"));
    });

    $summaryCards.on("click", function () {
        setFilter($(this).data("filter"));
    });

    $searchInput.on("input", function () {
        searchQuery = $(this).val().trim().toLowerCase();
        currentPage = 1;
        renderTable();
    });

    $paginationControls.on("click", ".sgg-bl-page-prev", function () {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    $paginationControls.on("click", ".sgg-bl-page-next", function () {
        currentPage++;
        renderTable();
    });

    $tableBody.on("click", ".sgg-bl-more-sources", function () {
        $(this).siblings(".sgg-bl-extra-sources").toggleClass("hidden-area");
    });

    $tableBody.on("click", ".sgg-bl-recheck", function () {
        const $btn = $(this);
        const url = $btn.data("url");

        $btn.prop("disabled", true);

        $.post(sggBrokenLinks.ajax_url, {
            action: "sgg_broken_links_recheck",
            nonce: sggBrokenLinks.nonce,
            url: url,
        }).done(function (response) {
            if (response.success) {
                applyResults(response.data);
            }
        }).always(function () {
            $btn.prop("disabled", false);
        });
    });

    function toggleIgnore(url, ignore) {
        $.post(sggBrokenLinks.ajax_url, {
            action: "sgg_broken_links_ignore",
            nonce: sggBrokenLinks.nonce,
            url: url,
            ignore: ignore ? 1 : 0,
        }).done(function (response) {
            if (response.success) {
                applyResults(response.data);

                if (!ignoredUrls.length) {
                    $ignoredSection.addClass("hidden-area");
                }
            }
        });
    }

    $tableBody.on("click", ".sgg-bl-ignore", function () {
        toggleIgnore($(this).data("url"), true);
    });

    $ignoredList.on("click", ".sgg-bl-unignore", function () {
        toggleIgnore($(this).data("url"), false);
    });

    $showIgnored.on("click", function (e) {
        e.preventDefault();
        $ignoredSection.toggleClass("hidden-area");
    });

    if (sggBrokenLinks.has_results) {
        loadResults();
    }
});
