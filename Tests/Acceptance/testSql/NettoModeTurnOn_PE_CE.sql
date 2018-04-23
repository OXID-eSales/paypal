DELETE FROM `oxconfig` WHERE `OXVARNAME` IN ('blShowNetPrice', 'blShowVATForDelivery');

INSERT IGNORE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('__fd147c4d0af42f3f27f5e8914fd35f', 1, '', 'blShowNetPrice', 'bool', 0x07),
('__f34735add4256a77a0096e7a2a2f8e', 1, '', 'blShowVATForDelivery', 'bool', 0x07);