<?xml version="1.0" encoding="UTF-8"?>
<report>
    <status>%status%</status>
    <date>%timestamp%</date>
    <backups>
        %%backup%%
        <backup>
            <name>%name%</name>
            <status>%status%</status>
        </backup>
        %%backup%%
    </backups>
    <errors>
        %%error%%
        <error class="%class%" message="%message%" file="%file%" line="%line%" />
        %%error%%
    </errors>
</report>
