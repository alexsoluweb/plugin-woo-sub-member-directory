<?php
/**
 * Member Directory template
 * 
 * @var $args['shortcode_args'] Array of shortcode arguments
 */
?>
<div id="wsmd-member-directory">
    <input id="wsmd-search-autocomplete" placeholder="Search places" type="text">
    <button id="wsmd-search-near-me">Search Near Me</button>
    <div id="wsmd-member-list">
        <!-- Member items will be dynamically populated here -->
    </div>
    <div id="mswd-map-container">
        <div id="wsmd-map" style="height: 400px;"></div>
    </div>
</div>
