<?php

use Maatwebsite\Excel\Facades\Excel;

// it('can import redirects', function () {
//    Excel::fake();
//
//    $this->actingAs($this->givenUser())
//        ->get('/users/import/xlsx');
//
//    Excel::assertImported('filename.xlsx', 'diskName');
//
//    Excel::assertImported('filename.xlsx', 'diskName', function(UsersImport $import) {
//        return true;
//    });
//
//    // When passing the callback as 2nd param, the disk will be the default disk.
//    Excel::assertImported('filename.xlsx', function(UsersImport $import) {
//        return true;
//    });
//
// });
