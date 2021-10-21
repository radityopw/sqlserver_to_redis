<?php 

if(count($argv) != 5){
    echo "parameter keliru".PHP_EOL;
    echo "php -f generate_template.php <nama schema> <nama tabel> <schema sumber> <nama kolom pk>".PHP_EOL;
    exit;
}

$schema = $argv[1];
$table = $argv[2];
$schema_src = $argv[3];
$pk = $argv[4];

$sql = "

-- buat schema jika belum dibuat

CREATE SCHEMA [".$schema."]
GO

-- buat tabel untuk cdc 

BEGIN TRANSACTION
SET QUOTED_IDENTIFIER ON
SET ARITHABORT ON
SET NUMERIC_ROUNDABORT OFF
SET CONCAT_NULL_YIELDS_NULL ON
SET ANSI_NULLS ON
SET ANSI_PADDING ON
SET ANSI_WARNINGS ON
COMMIT
BEGIN TRANSACTION
GO
CREATE TABLE ".$schema.".".$table."
	(
	id uniqueidentifier NOT NULL,
	parent_id bigint NOT NULL,
	status varchar(10) NOT NULL,
	waktu datetime NOT NULL,
	in_redis tinyint NOT NULL
	)  ON [PRIMARY]
GO
ALTER TABLE ".$schema.".".$table." ADD CONSTRAINT
	DF_".$table."_id DEFAULT NEWSEQUENTIALID() FOR id
GO
ALTER TABLE ".$schema.".".$table." ADD CONSTRAINT
	DF_".$table."_waktu DEFAULT getdate() FOR waktu
GO
ALTER TABLE ".$schema.".".$table." ADD CONSTRAINT
	DF_".$table."_in_redis DEFAULT 0 FOR in_redis
GO
ALTER TABLE ".$schema.".".$table." ADD CONSTRAINT
	PK_".$table."_1 PRIMARY KEY CLUSTERED 
	(
	id
	) WITH( STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]

GO

ALTER TABLE ".$schema.".".$table." SET (LOCK_ESCALATION = TABLE)
GO
COMMIT

-- trigger setelah insert 

SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TRIGGER ".$schema_src.".".$table."_insert 
   ON  ".$schema_src.".".$table."
   AFTER INSERT
AS 
BEGIN
	
	SET NOCOUNT ON;

    INSERT INTO ".$schema.".".$table."(parent_id,status)
	SELECT ID,'INSERT'
	FROM INSERTED

END
GO


-- trigger setelah update 


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TRIGGER ".$schema_src.".".$table."_update 
   ON  ".$schema_src.".".$table."
   AFTER UPDATE
AS 
BEGIN
	
	SET NOCOUNT ON;

    INSERT INTO ".$schema.".".$table."(parent_id,status)
	SELECT ID,'UPDATE'
	FROM INSERTED

END
GO


-- trigger setelah delete


SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO

CREATE TRIGGER ".$schema_src.".".$table."_delete 
   ON  ".$schema_src.".".$table."
   AFTER DELETE
AS 
BEGIN
	
	SET NOCOUNT ON;

    INSERT INTO ".$schema.".".$table."(parent_id,status)
	SELECT ID,'DELETE'
	FROM DELETED

END
GO

-- index untuk cdc 

CREATE NONCLUSTERED INDEX [NC_in_redis] ON [".$schema."].[".$table."]
(
	[in_redis] ASC,
	[waktu] ASC
)
INCLUDE([id],[parent_id],[status]) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF)




CREATE NONCLUSTERED INDEX [NC_waktu] ON [".$schema."].[".$table."]
(
	[waktu] ASC,
	[in_redis] ASC
)
INCLUDE([id],[parent_id],[status]) WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, SORT_IN_TEMPDB = OFF, DROP_EXISTING = OFF, ONLINE = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF)

GO


";

echo $sql.PHP_EOL;