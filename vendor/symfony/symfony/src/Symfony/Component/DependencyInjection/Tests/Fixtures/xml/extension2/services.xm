<?xml version="1.0" ?>

<container xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd">
  <services>
    <service id="extension2.foo" class="FooClass2">
      <argument type="service">
        <service class="BarClass2">
        </service>
      </argument>
    </service>
  </services>
</container>
