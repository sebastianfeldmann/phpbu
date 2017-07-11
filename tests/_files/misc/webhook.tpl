<myXml>
    <return>%status%</return>
    <backups>
    %%backup%%
        <backup>
            <status>%status%</status>
        </backup>
    %%backup%%
    </backups>
    <errors>
    %%error%%
        <error message="%message%" />
    %%error%%
    </errors>
</myXml>
