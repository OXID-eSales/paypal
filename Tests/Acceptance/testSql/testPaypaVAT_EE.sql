DELETE FROM `oxconfig` WHERE `OXVARNAME` IN ('blEnterNetPrice', 'blShowVATForDelivery', 'blDeliveryVatOnTop', 'blShowVATForPayCharge', 'blPaymentVatOnTop', 'blWrappingVatOnTop');

INSERT IGNORE INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('__47a1b4fd3e27983e7eea7ccb054d43', '1', '', 'blEnterNetPrice', 'bool', 0x07),
('__f6a9463c184724fefd00c06abc0c0a', '1', '', 'blShowVATForDelivery', 'bool', 0x07),
('__20e067acfdd1c9533cce8222b79afe', '1', '', 'blDeliveryVatOnTop', 'bool', 0x07),
('__f34735add4256a77a0096e7a2a2f8e', '1', '', 'blShowVATForPayCharge', 'bool', 0x07),
('__de848c6c8d04893361cc7194bb3e62', '1', '', 'blPaymentVatOnTop', 'bool', 0x07),
('__46c2222e90ef717f714567446da629', '1', '', 'blWrappingVatOnTop', 'bool', 0x07);