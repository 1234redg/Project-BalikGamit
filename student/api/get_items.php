function loadCards() {
    const $grid = $('#cardsGrid');
    
    // Clear and show loading
    $grid.html('<div class="cards-loading" id="spinner"><div class="spinner"></div> Connecting to server...</div>');
    $('#emptyState, #resultCount').hide();

    $.ajax({
        url: API_BASE + 'get_items.php',
        method: 'GET',
        data: {
            search: $('#searchInput').val().trim(),
            status: activeStatus,
            category_id: activeCategory
        },
        dataType: 'json',
        timeout: 3000, // 3 Seconds - stop the "forever" spin
        success: function(items) {
            $grid.empty();
            if (!items || items.length === 0) {
                $('#emptyState').show();
                return;
            }
            $('#resultNum').text(items.length);
            $('#resultCount').show();

            // Render your cards... (existing logic)
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error Output:", { status, error, response: xhr.responseText }); //[cite: 3]
            
            let displayMsg = "Failed to load.";
            if (status === 'timeout') {
                displayMsg = "Server Timeout: The database is taking too long to respond.";
            } else if (xhr.responseJSON && xhr.responseJSON.error) {
                displayMsg = xhr.responseJSON.error;
            } else {
                displayMsg = error || "Unknown Server Error";
            }

            $grid.html(`
                <div style="text-align:center; padding:50px; color:#ef4444; background:#fef2f2; border-radius:8px;">
                    <i class="fa-solid fa-plug-circle-exclamation" style="font-size:40px; margin-bottom:15px;"></i>
                    <p style="font-size:18px; font-weight:bold; margin-bottom:5px;">Connection Issue</p>
                    <p style="font-size:14px; color:#b91c1c;">${displayMsg}</p>
                    <button onclick="loadCards()" style="margin-top:15px; padding:8px 16px; cursor:pointer;">Try Again</button>
                </div>
            `);
        }
    });
}