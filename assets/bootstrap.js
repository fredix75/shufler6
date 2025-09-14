import { startStimulusApp } from '@symfony/stimulus-bundle';

const app = startStimulusApp();

import Autocomplete from 'stimulus-autocomplete'

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

app.register('autocomplete', Autocomplete);
