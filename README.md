PHP Paginator BS4
=================

A lightweight PHP paginator, for generating pagination controls in the style of Stack Overflow and Flickr using Bootstrap4 CSS classes, forked from https://github.com/jasongrimes/php-paginator. 
The "first" and "last" page links are shown inline as page numbers, and excess page numbers are replaced by ellipses.

## Improvements to original package
* Use Bootstrap4 CSS classes
* Fluid interface
* Allow specification of outer and inner class additions
* Better PHPDocs

## Screenshots

These examples show how the paginator handles overflow when there are a lot of pages.
They're rendered using the sample templates provided in the [examples](examples/) directory,
which depend on Twitter Bootstrap. 
You can easily use your own custom HTML to render the pagination control instead.

Default template:

<img src="examples/screenshot-default-first.png" width="447"><br/>
<img src="examples/screenshot-default-mid.png" width="597"><br/>
<img src="examples/screenshot-default-last.png" width="534"><br/>

Small template (useful for mobile interfaces):

<img src="examples/screenshot-small-first.png" width="157"><br/>
<img src="examples/screenshot-small-mid.png" width="220"><br/>
<img src="examples/screenshot-small-last.png" width="157"><br/>

The small template renders the page number as a select list to save space:

<img src="examples/screenshot-small-mid-open.png" width="218">


## Installation

Install with composer: 

    composer require "innobrig/paginator:~1.0"

## Basic usage

Here's a quick example using the defaults:

    <?php
    
    require '../vendor/autoload.php';

    use InnoBrig\Paginator;

    $totalItems = 1000;
    $itemsPerPage = 50;
    $currentPage = 8;
    $urlPattern = '/foo/page/(:num)';

    $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);

    ?>
    <html>
      <head>
        <!-- The default, built-in template supports the Twitter Bootstrap pagination styles. -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
      </head>
      <body>

        <?php 
          // Example of rendering the pagination control with the built-in template.
          // See below for information about using other templates or custom rendering.

          echo $paginator; 
        ?>
        
      </body>
    </html>

This will output the following:

<img src="examples/screenshot-default-mid.png" width="597">

    <ul class="pagination">
      <li class="page-item">
        <a class="page-link" href="/foo/page/7#" aria-label="Previous">
          <span aria-hidden="true">&laquo;</span>
          <span class="sr-only">Previous</span>
        </a>
      </li>
      <li class="page-item"><a class="page-link" href="#">1</a></li>
      <li class="page-item"><a class="page-link" href="#">2</a></li>
      <li class="page-item"><a class="page-link" href="#">3</a></li>
      <li class="page-item">
        <a class="page-link" href="#" aria-label="Next">
          <span aria-hidden="true">&raquo;</span>
          <span class="sr-only">Next</span>
        </a>
      </li>
    </ul>

To render it with one of the other example templates, just make sure the variable is named `$paginator` and then include the template file:

    $paginator = new Paginator($totalItems, $itemsPerPage, $currentPage, $urlPattern);
    
    include '../vendor/jasongrimes/paginator/examples/pagerSmall.phtml';
    
<img src="examples/screenshot-small-mid.png" width="220"><br/>

If the example templates don't suit you, you can iterate over the paginated data to render your own pagination control.

## Rendering a custom pagination control

Use `$paginator->getPages()`, `$paginator->getNextUrl()`, and `$paginator->getPrevUrl()` to render a pagination control with your own HTML.
For example:

    <ul class="pagination">
        <?php if ($paginator->getPrevUrl()): ?>
            <li><a href="<?php echo $paginator->getPrevUrl(); ?>">&laquo; Previous</a></li>
        <?php endif; ?>

        <?php foreach ($paginator->getPages() as $page): ?>
            <?php if ($page['url']): ?>
                <li <?php echo $page['isCurrent'] ? 'class="active"' : ''; ?>>
                    <a href="<?php echo $page['url']; ?>"><?php echo $page['num']; ?></a>
                </li>
            <?php else: ?>
                <li class="disabled"><span><?php echo $page['num']; ?></span></li>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($paginator->getNextUrl()): ?>
            <li><a href="<?php echo $paginator->getNextUrl(); ?>">Next &raquo;</a></li>
        <?php endif; ?>
    </ul>
    
    <p>
        <?php echo $paginator->getTotalItems(); ?> found.
        
        Showing 
        <?php echo $paginator->getCurrentPageFirstItem(); ?> 
        - 
        <?php echo $paginator->getCurrentPageLastItem(); ?>.
    </p>


See the [examples](examples) directory for more sample templates.

## Pages data structure

    $paginator->getPages();

`getPages()` returns a data structure like the following:

    array ( 
        array ('num' => 1,     'url' => '/foo/page/1',  'isCurrent' => false),
        array ('num' => '...', 'url' => NULL,           'isCurrent' => false),
        array ('num' => 5,     'url' => '/foo/page/5',  'isCurrent' => false),
        array ('num' => 6,     'url' => '/foo/page/6',  'isCurrent' => false),
        array ('num' => 7,     'url' => '/foo/page/7',  'isCurrent' => false),
        array ('num' => 8,     'url' => '/foo/page/8',  'isCurrent' => true),
        array ('num' => 9,     'url' => '/foo/page/9',  'isCurrent' => false),
        array ('num' => 10,    'url' => '/foo/page/10', 'isCurrent' => false),
        array ('num' => 11,    'url' => '/foo/page/11', 'isCurrent' => false),
        array ('num' => 12,    'url' => '/foo/page/12', 'isCurrent' => false),
        array ('num' => '...', 'url' => NULL,           'isCurrent' => false),
        array ('num' => 20,    'url' => '/foo/page/20', 'isCurrent' => false),
    )

## Customizing the number of pages shown

By default, no more than 10 pages are shown, including the first and last page, with the overflow replaced by ellipses.
To change the default number of pages:

    $paginator->setMaxPagesToShow(5);

