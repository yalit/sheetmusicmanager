import { registerReactControllerComponents } from '@symfony/ux-react';
import './stimulus_bootstrap.js';
import './admin/pdf_field_input.js'
import './admin/autocomplete_field_input.js'
import './admin/collection_table.js'

/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';



registerReactControllerComponents();
