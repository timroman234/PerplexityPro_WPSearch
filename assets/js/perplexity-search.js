// Always clear sidebar search input on page load or when restored from bfcache
function clearSidebarSearchInput() {
    var sidebarInput = document.querySelector('.sidebar-pp-search-input');
    if (sidebarInput) sidebarInput.value = '';
}
window.addEventListener('pageshow', function(event) {
    clearSidebarSearchInput();
});

jQuery(document).ready(function($) {
    const searchForm = $('.perplexity-search-form');
    const searchInput = $('.perplexity-search-input');
    const resultsContainer = $('.perplexity-search-results');
    
    // Add search buttons
    const searchButtons = $('<div class="perplexity-search-buttons"></div>');
    const clearButton = $('<button type="button" class="perplexity-search-clear" aria-label="Clear search"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>');
    const submitButton = $('<button type="submit" class="perplexity-search-submit" aria-label="Search"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></button>');
    
    searchButtons.append(clearButton, submitButton);
    searchForm.append(searchButtons);
    
    // Clear button functionality
    clearButton.on('click', function() {
        searchInput.val('').focus();
        resultsContainer.empty();
    });
    
    // Sidebar clear button functionality
    $(document).on('click', '.sidebar-pp-search-clear', function() {
        var $input = $(this).closest('.sidebar-pp-search-form').find('.sidebar-pp-search-input');
        $input.val('').trigger('input').focus();
    });
    
    // Search form submission
    searchForm.on('submit', function(e) {
        e.preventDefault();
        const query = searchInput.val().trim();
        
        if (!query) return;
        
        // Show loading state
        resultsContainer.html('<div class="perplexity-loading">Searching...</div>');
        
        $.ajax({
            url: perplexitySearchData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'perplexity_search',
                nonce: perplexitySearchData.nonce,
                query: query
            },
            success: function(response) {
                if (response.success && response.data) {
                    displayResults(response.data);
                } else {
                    resultsContainer.html('<div class="perplexity-error">No results found.</div>');
                }
            },
            error: function() {
                resultsContainer.html('<div class="perplexity-error">An error occurred. Please try again.</div>');
            }
        });
    });
    
    function tryParseJSON(text) {
        try {
            // Find JSON-like content between backticks or quotes
            const jsonMatch = text.match(/```json\s*(\{[\s\S]*?\})\s*```|`(\{[\s\S]*?\})`|"(\{[\s\S]*?\})"/);
            if (jsonMatch) {
                const jsonStr = (jsonMatch[1] || jsonMatch[2] || jsonMatch[3]).trim();
                return JSON.parse(jsonStr);
            }
            
            // If no JSON found in backticks/quotes, try parsing the entire content
            return JSON.parse(text);
        } catch (e) {
            console.error('JSON parsing failed:', e);
            return null;
        }
    }
    
    function displayResults(data) {
        let html = '';
        
        // Try to parse the content as JSON if it's a string
        if (data.choices && data.choices[0] && data.choices[0].message) {
            const content = data.choices[0].message.content;
            const parsedData = tryParseJSON(content);
            
            if (parsedData && parsedData.summary) {
                // Display the summary
                html += '<div class="perplexity-summary">';
                html += '<h3>ANSWER</h3>';
                html += '<div class="perplexity-answer">' + parsedData.summary + '</div>';
                html += '</div>';
                
                // Display related articles
                if (parsedData.articles && parsedData.articles.length > 0) {
                    html += '<div class="perplexity-related-articles">';
                    html += '<h3>Related Articles</h3>';
                    html += '<div class="perplexity-articles-grid">';
                    
                    parsedData.articles.forEach(function(article) {
                        if (!article.url || !article.title) return;
                        
                        html += '<div class="perplexity-article-item">';
                        if (article.image_url && article.image_url !== "Not available") {
                            html += '<div class="perplexity-article-image">';
                            html += '<img src="' + article.image_url + '" alt="Article thumbnail">';
                            html += '</div>';
                        }
                        html += '<div class="perplexity-article-content">';
                        html += '<a href="' + article.url + '" class="perplexity-article-title">' + article.title + '</a>';
                        if (article.date) {
                            html += '<div class="perplexity-article-date">' + article.date + '</div>';
                        }
                        if (article.snippet) {
                            html += '<p class="perplexity-article-snippet">' + article.snippet + '</p>';
                        }
                        html += '</div>';
                        html += '</div>';
                    });
                    
                    html += '</div>';
                    html += '</div>';
                }
            } else {
                // Fallback to displaying raw content if JSON parsing fails
                html += '<div class="perplexity-summary">';
                html += '<h3>ANSWER</h3>';
                html += '<div class="perplexity-answer">' + content + '</div>';
                html += '</div>';
            }
        }
        
        // Display any additional citations from the API response
        if (data.citations && data.citations.length > 0) {
            if (!html.includes('Related Articles')) {
                html += '<div class="perplexity-related-articles">';
                html += '<h3>Related Articles</h3>';
                html += '<div class="perplexity-articles-grid">';
                
                data.citations.forEach(function(article) {
                    if (!article.url || !article.title) return;
                    
                    html += '<div class="perplexity-article-item">';
                    if (article.image_url && article.image_url !== "Not available") {
                        html += '<div class="perplexity-article-image">';
                        html += '<img src="' + article.image_url + '" alt="Article thumbnail">';
                        html += '</div>';
                    }
                    html += '<div class="perplexity-article-content">';
                    html += '<a href="' + article.url + '" class="perplexity-article-title">' + article.title + '</a>';
                    if (article.date) {
                        html += '<div class="perplexity-article-date">' + article.date + '</div>';
                    }
                    if (article.snippet) {
                        html += '<p class="perplexity-article-snippet">' + article.snippet + '</p>';
                    }
                    html += '</div>';
                    html += '</div>';
                });
                
                html += '</div>';
                html += '</div>';
            }
        }
        
        resultsContainer.html(html);
    }

    // Auto-trigger search if 'q' parameter is present in the URL
    function getQueryParam(name) {
        const url = new URL(window.location.href);
        return url.searchParams.get(name);
    }
    const sidebarQuery = getQueryParam('q');
    if (sidebarQuery && searchInput.length) {
        searchInput.val(sidebarQuery);
        searchForm.trigger('submit');
    }
});
