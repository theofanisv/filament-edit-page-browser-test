<?php

use App\Filament\Resources\Vehicles\Pages\EditVehicle;
use App\Models\Vehicle;
use Filament\Facades\Filament;
use Theograms\EditPageTester\EditPageTester;
use Theograms\EditPageTester\Examples\ParkingFieldHandler;


beforeEach(function () {
    Filament::setCurrentPanel('admin');
    // $this->actingAs('some user with permissions to edit vehicles');
    $model = Vehicle::factory()->create()->refresh();
    $this->edit_page_browser = EditPageTester::make($this->model)->withCustomFieldHandler(new ParkingFieldHandler);
});

test(class_basename(EditVehicle::class) . ' test preview', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    /** @var array $fields Fields as defined in the corresponding `Resource::form`/ */
    $fields = ['plate', 'brand', 'model', 'color', 'banned_at', 'description', 'vehicle_type', 'partner', 'preferred_spot', 'customer'];
    $this->edit_page_browser
        ->requiredVisibleFields($fields)
        ->testPreview();
});

test(class_basename(EditVehicle::class) . ' test save', function () {
    visit([]);

    $new_model = Vehicle::factory()->make();

    $this->edit_page_browser
        ->withNew($new_model)
        ->testSave();
});
