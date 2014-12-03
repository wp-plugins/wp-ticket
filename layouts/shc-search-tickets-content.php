<?php $ent_attrs = get_option('wp_ticket_com_attr_list'); ?>
<tr>
    <td class="search-results-row"><a href="<?php echo get_permalink(); ?>" target="_blank"><?php echo esc_html(rwmb_meta('emd_ticket_id')); ?>
</a></td>
    <td class="search-results-row"><?php echo strip_tags(get_the_term_list(get_the_ID() , 'ticket_status', '', ' ', '')); ?></td>
    <td class="search-results-row"><?php echo get_the_title(); ?></td>
    <td class="search-results-row"><?php echo get_the_modified_date(); ?></td>
</tr>