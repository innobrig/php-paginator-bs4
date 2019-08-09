<?php

namespace InnoBrig\Paginator;

class Paginator
{
    const NUM_PLACEHOLDER = '(:num)';

    protected $totalItems;
    protected $numPages;
    protected $itemsPerPage;
    protected $currentPage;
    protected $urlPattern;
    protected $maxPagesToShow = 10;
    protected $previousText = 'Previous';
    protected $nextText = 'Next';
    protected $classListOuter = [];
    protected $classListInner = [];

    /**
     * @param int $totalItems The total number of items.
     * @param int $itemsPerPage The number of items per page.
     * @param int $currentPage The current page number.
     * @param string $urlPattern A URL for each page, with (:num) as a placeholder for the page number. Ex. '/foo/page/(:num)'
     * @param array $classListOuter An array of class names you want to add to the pagination <ul> wrapper.
     * @param array $classListInner An array of class names you want to add to the pagination <li> elements.
     */
    public function __construct(int $totalItems, int $itemsPerPage, int $currentPage, string $urlPattern = '', array $classListOuter=[], array $classListInner=[])
    {
        $this->totalItems     = $totalItems;
        $this->itemsPerPage   = $itemsPerPage;
        $this->currentPage    = $currentPage;
        $this->urlPattern     = $urlPattern;
        $this->classListOuter = $classListOuter;
        $this->classListInner = $classListInner;

        $this->updateNumPages();
    }

    protected function updateNumPages() : void
    {
        $this->numPages = ($this->itemsPerPage == 0 ? 0 : (int) ceil($this->totalItems/$this->itemsPerPage));
    }

    /**
     * @param int $maxPagesToShow
     * @throws \InvalidArgumentException if $maxPagesToShow is less than 3.
     * @return Paginator
     */
    public function setMaxPagesToShow(int $maxPagesToShow) : Paginator
    {
        if ($maxPagesToShow < 3) {
            throw new \InvalidArgumentException('maxPagesToShow cannot be less than 3.');
        }
	    $this->maxPagesToShow = $maxPagesToShow;

	    return $this;
    }

    /**
     * @return int
     */
    public function getMaxPagesToShow() : int
    {
        return $this->maxPagesToShow;
    }

    /**
     * @param int $currentPage
     * @return Paginator
     */
    public function setCurrentPage(int $currentPage) : Paginator
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentPage() : int
    {
        return $this->currentPage;
    }

    /**
     * @param int $itemsPerPage
     * @return Paginator
     */
    public function setItemsPerPage(int $itemsPerPage) : Paginator
    {
        $this->itemsPerPage = $itemsPerPage;
        $this->updateNumPages();

        return $this;
    }

    /**
     * @return int
     */
    public function getItemsPerPage() : int
    {
        return $this->itemsPerPage;
    }

    /**
     * @param int $totalItems
     * @return Paginator
     */
    public function setTotalItems(int $totalItems) : Paginator
    {
        $this->totalItems = $totalItems;
        $this->updateNumPages();

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalItems() : int
    {
        return $this->totalItems;
    }

    /**
     * @return int
     */
    public function getNumPages() : int
    {
        return $this->numPages;
    }

    /**
     * @param string $urlPattern
     * @return Paginator
     */
    public function setUrlPattern(string $urlPattern) : Paginator
    {
        $this->urlPattern = $urlPattern;

        return $this;
    }

    /**
     * @return string
     */
    public function getUrlPattern() : string
    {
        return $this->urlPattern;
    }

    /**
     * @param int $pageNum
     * @return string
     */
    public function getPageUrl(int $pageNum) : string
    {
        return str_replace(self::NUM_PLACEHOLDER, $pageNum, $this->urlPattern);
    }

    /**
     * @return int|null
     */
    public function getNextPage() : ?int
    {
        if ($this->currentPage < $this->numPages) {
            return $this->currentPage + 1;
        }

        return null;
    }

    /**
     * @return int|null
     */
    public function getPrevPage() : ?int
    {
        if ($this->currentPage > 1) {
            return $this->currentPage - 1;
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getNextUrl() : ?string
    {
        if (!$this->getNextPage()) {
            return null;
        }

        return $this->getPageUrl($this->getNextPage());
    }

    /**
     * @return string|null
     */
    public function getPrevUrl() : ?string
    {
        if (!$this->getPrevPage()) {
            return null;
        }

        return $this->getPageUrl($this->getPrevPage());
    }

    /**
     * Get an array of paginated page data.
     *
     * Example:
     * array(
     *     array ('num' => 1,     'url' => '/example/page/1',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 3,     'url' => '/example/page/3',  'isCurrent' => false),
     *     array ('num' => 4,     'url' => '/example/page/4',  'isCurrent' => true ),
     *     array ('num' => 5,     'url' => '/example/page/5',  'isCurrent' => false),
     *     array ('num' => '...', 'url' => NULL,               'isCurrent' => false),
     *     array ('num' => 10,    'url' => '/example/page/10', 'isCurrent' => false),
     * )
     *
     * @return array
     */
    public function getPages() : array
    {
        $pages = array();

        if ($this->numPages <= 1) {
            return array();
        }

        if ($this->numPages <= $this->maxPagesToShow) {
            for ($i = 1; $i <= $this->numPages; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
        } else {

            // Determine the sliding range, centered around the current page.
            $numAdjacents = (int) floor(($this->maxPagesToShow - 3) / 2);

            if ($this->currentPage + $numAdjacents > $this->numPages) {
                $slidingStart = $this->numPages - $this->maxPagesToShow + 2;
            } else {
                $slidingStart = $this->currentPage - $numAdjacents;
            }
            if ($slidingStart < 2) $slidingStart = 2;

            $slidingEnd = $slidingStart + $this->maxPagesToShow - 3;
            if ($slidingEnd >= $this->numPages) $slidingEnd = $this->numPages - 1;

            // Build the list of pages.
            $pages[] = $this->createPage(1, $this->currentPage == 1);
            if ($slidingStart > 2) {
                $pages[] = $this->createPageEllipsis();
            }
            for ($i = $slidingStart; $i <= $slidingEnd; $i++) {
                $pages[] = $this->createPage($i, $i == $this->currentPage);
            }
            if ($slidingEnd < $this->numPages - 1) {
                $pages[] = $this->createPageEllipsis();
            }
            $pages[] = $this->createPage($this->numPages, $this->currentPage == $this->numPages);
        }


        return $pages;
    }


    /**
     * Create a page data structure.
     *
     * @param int $pageNum
     * @param bool $isCurrent
     * @return array
     */
    protected function createPage(int $pageNum, bool $isCurrent = false) : array
    {
        return array(
            'num' => $pageNum,
            'url' => $this->getPageUrl($pageNum),
            'isCurrent' => $isCurrent,
        );
    }

    /**
     * @return array
     */
    protected function createPageEllipsis() : array
    {
        return array(
            'num' => '...',
            'url' => null,
            'isCurrent' => false,
        );
    }

    /**
     * Render an HTML pagination control.
     *
     * @return string
     */
    public function toHtml() : string
    {
        if ($this->numPages <= 1) {
            return '';
        }

	    $html        = '<ul class="pagination ' . implode(' ', $this->classListOuter) . '">';
	    $urlPrev     = $this->getPrevUrl();
        $classAddon  = implode(' ', $this->classListInner);
	    if ($urlPrev) {
            $encoded = htmlspecialchars($urlPrev);
	        $text    = $this->previousText;
	        $class   = "page-link $classAddon";
            $html   .= "<li><a class='$class' href='$encoded' aria-label='$text'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>$text</span></a></li>";
        }

	    foreach ($this->getPages() as $page) {
            $encoded = htmlspecialchars($page['url']);
            $text    = htmlspecialchars($page['num']);
            $class   = "page-item $classAddon";
            if ($page['isCurrent']) {
                $class .= ' active';
            }
            if ($page['url']) {
                $html .= "<li class='$class'><a class='page-link' href='$encoded'>$text</a></li>";
            } else {
                $html .= "<li class='$class disabled'><span>'$text</span></li>";
            }
        }

	    $urlNext = $this->getNextUrl();
        if ($urlNext) {
            $encoded = htmlspecialchars($urlNext);
            $text    = $this->nextText;
	        $class   = "page-link $classAddon";
            $html   .= "<li><a class='$class' href='$encoded' aria-label='$text'><span aria-hidden='true'>&raquo;</span><span class='sr-only'>$text</span></a></li>";
        }
        $html .= '</ul>';

        return $html;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->toHtml();
    }

    /**
     * @return int|null
     */
    public function getCurrentPageFirstItem() : ?int
    {
        $first = ($this->currentPage - 1) * $this->itemsPerPage + 1;

        if ($first > $this->totalItems) {
            return null;
        }

        return $first;
    }

    /**
     * @return int|null
     */
    public function getCurrentPageLastItem() : ?int
    {
        $first = $this->getCurrentPageFirstItem();
        if ($first === null) {
            return null;
        }

        $last = $first + $this->itemsPerPage - 1;
        if ($last > $this->totalItems) {
            return $this->totalItems;
        }

        return $last;
    }

    /**
     * @param string $text
     * @return Paginator
     */
    public function setPreviousText(string $text) : Paginator
    {
        $this->previousText = $text;

        return $this;
    }

    /**
     * @param string $text
     * @return Paginator
     */
    public function setNextText(string $text) : Paginator
    {
        $this->nextText = $text;

        return $this;
    }
}

