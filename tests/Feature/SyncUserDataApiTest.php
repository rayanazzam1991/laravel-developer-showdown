<?php

describe('Sync User Data Api rate limit', function () {

    test('send less than 50000 CPH successfully', function () {

    });
    test('prevent send more than 50000 CPH', function () {

    });

    it('should resend failed updates if we have not reach the limit for this hour', function () {

    });

    it('should resend failed updates in previous hour in next hour if we get over the limit', function () {

    });

    it('should save the data of user updated in a queue and not send it  directly', function () {

    });

    test('cronjob to pick a chunk of data from the queue every 15 minutes and send them to 3rd party API', function () {

    });

    it('should trigger action of sync if the unsent updates reaches 50000 before the 1 hour', function () {

    });

    it('should send no more than 20000 CPH and store the reset in the queue if we get more
    than 40000 in the first 15 minutes', function () {

    });

    it('should prevent cronjob from working if we have a limit flag raised', function () {

    });

    it('should give the ability to change over CPH plan', function () {

    });

    test('send 20000 CPH for this hour if the plan is normal', function () {

    });

    test('send 10000 CPH for this hour if the plan is medium', function () {

    });

    test('send 5000 CPH for this hour if the plan is high', function () {

    });

});
