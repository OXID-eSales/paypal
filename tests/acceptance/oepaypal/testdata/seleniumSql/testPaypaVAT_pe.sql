DELETE FROM `oxconfig`
WHERE `OXVARNAME` IN ('blEnterNetPrice', 'blShowVATForDelivery', 'blDeliveryVatOnTop', 'blShowVATForPayCharge', 'blPaymentVatOnTop', 'blWrappingVatOnTop');

INSERT INTO `oxconfig` (`OXID`, `OXSHOPID`, `OXMODULE`, `OXVARNAME`, `OXVARTYPE`, `OXVARVALUE`) VALUES
('__47a1b4fd3e27983e7eea7ccb054d43', 'oxbaseshop', '', 'blEnterNetPrice', 'bool', 0x07),
('__f6a9463c184724fefd00c06abc0c0a', 'oxbaseshop', '', 'blShowVATForDelivery', 'bool', 0x07),
('__20e067acfdd1c9533cce8222b79afe', 'oxbaseshop', '', 'blDeliveryVatOnTop', 'bool', 0x07),
('__f34735add4256a77a0096e7a2a2f8e', 'oxbaseshop', '', 'blShowVATForPayCharge', 'bool', 0x07),
('__de848c6c8d04893361cc7194bb3e62', 'oxbaseshop', '', 'blPaymentVatOnTop', 'bool', 0x07),
('__46c2222e90ef717f714567446da629', 'oxbaseshop', '', 'blWrappingVatOnTop', 'bool', 0x07);