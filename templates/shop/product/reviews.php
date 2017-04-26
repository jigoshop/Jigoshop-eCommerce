<?php
/**
 * @var $currentTab string Currently selected tab.
 * @var $product \Jigoshop\Entity\Product Currently displayed product.
 * @var $reviews \Jigoshop\Entity\Product\Review[]
 */

$commenter = wp_get_current_commenter();
$req = get_option('require_name_email');
$aria_req = ( $req ? " aria-required='true'" : '' );
?>
<div role="tabpanel" id="tab-reviews" class="tab-pane<?php $currentTab == 'reviews' and print ' active'; ?>">
    <?php foreach($reviews as $review) : ?>
        <div class="review">
            <div class="author pull-left"><?= $review->getComment()->comment_author; ?></div>
            <div class="ratting pull-right">
                <?php for($i = 1; $i <= 5; $i++): ?>
                    <span class="glyphicon glyphicon-star<?php print $review->getRating() < $i ? '-empty' : ''; ?>"></span>
                <?php endfor; ?>
            </div>
            <div class="clear"></div>
            <div class="review well well-sm">
                <?= nl2br($review->getComment()->comment_content); ?>
            </div>
        </div>
    <?php endforeach; ?>
    <div class="comment-form">
        <?php comment_form([
            'comment_field' => '<p class="comment-form-rating"><label for="rating">' . __( 'Rating', 'jigoshop' ) . '</label>' .
                '<a href="#" data-rating="1"><span class="glyphicon glyphicon-star"></span></a>'.
                '<a href="#" data-rating="2"><span class="glyphicon glyphicon-star-empty"></span></a>'.
                '<a href="#" data-rating="3"><span class="glyphicon glyphicon-star-empty"></span></a>'.
                '<a href="#" data-rating="4"><span class="glyphicon glyphicon-star-empty"></span></a>'.
                '<a href="#" data-rating="5"><span class="glyphicon glyphicon-star-empty"></span></a>'.
                '<select id="rating" name="rating"' . $aria_req . ' class="not-active">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>'.
                '<p class="comment-form-comment"><label for="comment">' . _x( 'Review', 'noun' ) .
                '</label><textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
            'fields' => [
                'author' =>
                    '<p class="comment-form-author"><label for="author">' . __( 'Name', 'domainreference' ) . '</label> ' .
                    ( $req ? '<span class="required">*</span>' : '' ) .
                    '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .
                    '" size="30"' . $aria_req . ' /></p>',
                'email' =>
                    '<p class="comment-form-email"><label for="email">' . __( 'Email', 'domainreference' ) . '</label> ' .
                    ( $req ? '<span class="required">*</span>' : '' ) .
                    '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .
                    '" size="30"' . $aria_req . ' /></p>',
            ]
        ]); ?>
    </div>
</div>
