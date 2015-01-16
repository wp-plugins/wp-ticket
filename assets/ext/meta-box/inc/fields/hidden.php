<?php
// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'RWMB_Hidden_Field' ) )
{
	class RWMB_Hidden_Field extends RWMB_Field
	{
		/**
		 * Get field HTML
		 *
		 * @param mixed  $meta
		 * @param array  $field
		 *
		 * @return string
		 */
		static function html( $meta, $field )
		{
			global $post;
                        if(!empty($field['hidden_func']))
                        {
				if(!empty($field['no_update']) && $field['no_update'] == 1 && !empty($meta))
				{ 
					//don't do anything
                                	$val = $meta;
				}
				else
				{	
					$val = emd_get_hidden_func($field['hidden_func']);
					if($val == 'emd_uid')
					{
						if(empty($meta))
						{
							$val =  uniqid($post->ID,false);
						}
						else
						{
							$val = $meta;
						}
					}
				}
                        }
                        else
                        {
                                $val = $meta;
                        }

                        return sprintf(
                                '<input type="hidden" class="rwmb-hidden" name="%s" id="%s" value="%s" />',
                                $field['field_name'],
                                $field['id'],
                                $val
                        );
		}
	}
}
