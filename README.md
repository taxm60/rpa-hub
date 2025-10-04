# 專案檔案說明

- **index.php**  
  主程式，提供 Web UI 介面，包含登入驗證以及管理 RPA token 的列表與操作。

- **init_db.php**  
  資料庫初始化腳本，用來建立 SQLite 資料表與初始結構。

- **rpa-manage.php**  
  API 介面，處理 token 的建立、刷新與關閉，供前端與外部系統呼叫。

- **token-helper.py**  
  Python 命令列工具，用於呼叫上述 API，方便在命令列透過 Python 操作 token。
