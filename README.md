# bsc components
Die Components sind ebenso wie Base51 Tafelsilber das nicht als opensource für redaxo 5 bereitgestellt werden soll.

Anders ist es da mit den Definitions, diese dürfen öffentlich sein und als OpenSource unter MIT bereitgestellt werden. Sie sind relativ untinteressant für normale Entwickler und alleine kann man wenig damit machen.

Mit hilfe von Base51, Components und Definitions lässt sich mit Hilfe von Redaxo einer halbwegs "erwachsenen" Webseiten-Webservice-Anwendung flott Aufbauen.

Die Idee "Website as a Service" mit Redaxo zu realisieren ist nicht abwägig, man kann die Redaxo Resourcen gut für den Webpart nutzen und mit nahezu null OAuth2 und fancy Frontend-Zauberkram eine moderne Webanwendung mit API Calls aufbauen. Dafür ist nur der Session Login mit YCom nötig. Es werden dann alle API Calls die vom Origin Host aus für die SessionAuth ankommen ohne Bearer Token so .   