document.addEventListener('DOMContentLoaded', function() {
    // Function to handle the global search results
    function handleGlobalSearchResults() {
        // Wait for the search results to be loaded
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Check if the search results container is present
                    const searchResultsContainer = document.querySelector('[data-global-search-results]');
                    
                    if (searchResultsContainer) {
                        // Get all search result items
                        const searchResultItems = searchResultsContainer.querySelectorAll('[data-global-search-result]');
                        
                        // If there's only one result, click on it to redirect
                        if (searchResultItems.length === 1) {
                            const link = searchResultItems[0].querySelector('a');
                            if (link) {
                                link.click();
                            }
                        }
                    }
                }
            });
        });
        
        // Observe the body for changes
        observer.observe(document.body, { childList: true, subtree: true });
    }
    
    // Initialize the handler
    handleGlobalSearchResults();
});
