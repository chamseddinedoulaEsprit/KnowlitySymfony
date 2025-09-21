import { Application } from '@hotwired/stimulus'; // Update this to use Hotwire's Stimulus
import { definitionsFromContext } from 'stimulus/webpack-helpers';

const application = Application.start();
const context = require.context('./controllers', true, /\.js$/);
application.load(definitionsFromContext(context));
