<?php

namespace PasswordlessLogin\adapter;

use ApiTestCase;
use MediaWiki\MediaWikiServices;
use PasswordlessLogin\model\Device;
use PasswordlessLogin\model\DevicesRepository;
use User;

/**
 * @group API
 * @group Database
 * @group medium
 *
 * @covers \PasswordlessLogin\adapter\ApiPasswordlessLogin
 */
class ApiPasswordlessLoginTest extends ApiTestCase {
	/** @var DevicesRepository */
	private $deviceRepository;

	protected function setUp() {
		parent::setUp();
		$this->deviceRepository =
			MediaWikiServices::getInstance()->getService( DevicesRepository::SERVICE_NAME );
	}

	public function testInvalidPairToken() {
		$result = $this->doApiRequest( [
			'action' => 'passwordlesslogin',
			'pairToken' => 'invalid',
			'deviceId' => 'A_DEVICE_ID'
		] );

		$this->assertEquals( 'Failed', $result[0]['register']['result'] );
	}

	public function testFillsDeviceId() {
		$device = Device::forUser( User::newFromName( 'UTSysop' ) );
		$this->deviceRepository->save( $device );

		$result = $this->doApiRequest( [
			'action' => 'passwordlesslogin',
			'pairToken' => $device->getPairToken(),
			'deviceId' => 'DEVICE_ID',
		] );

		$this->assertEquals( 'Success', $result[0]['register']['result'] );
		$registeredDevice = $this->deviceRepository->findByUserId( $device->getUserId() );
		$this->assertEquals( 'DEVICE_ID', $registeredDevice->getDeviceId() );
	}
}