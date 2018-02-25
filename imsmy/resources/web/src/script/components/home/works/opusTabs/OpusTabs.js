import React, { Component } from 'react'
import { Link } from 'react-router'
import { Button, Tabs, message } from 'antd'
const TabPane = Tabs.TabPane
import Tables from '../tables/Tables'
import Fetch from 'utils/fetch'

class OpusTabs extends Component{
  constructor(props){
    super(props)
    this.state={
      activeKey:'1',
      allNum:'',
      failNum:'',
      auditNum:'',
      alreadyUsedRoom:'',
      selectKeys:false,
      selectNum:'',
      refreshTable:false,
      selectBatchId:[]
    }
  }
  //选中tabPane
  handleChangeActiveKey = (key)=>{
    this.setState({
      activeKey:key
    })
  }
  //批量操作
  handleChangeBatchOperation=(value,num)=>{
    this.setState({
      selectKeys:value,
      selectNum:num
    })
  }
  //刷新表格
  handleChangeRefreshTableState=(value)=>{
    this.setState({
      refreshTable:value
    })
  }
  //获取批量操作选中的Id
  handleGetSelectRowsId=(value)=>{
    // console.log(value,'sss');
    this.setState({
      selectBatchId:value
    })
  }
  //批量删除
  handleBatchDeleteSelectRows=()=>{
    // console.log(this.state.selectBatchId);
    const BatchId = this.state.selectBatchId
    let formData = new FormData()
    if(BatchId.length===1){
      const selectOnlyId = BatchId.toString()
      formData.append('id',selectOnlyId)
    }else if(BatchId.length>1){
      const selectBatch = BatchId.join("|")
      formData.append('id',selectBatch)
    }
    Fetch.post({
      uri:'/api/test/delete',
      callback:(res)=>{
        console.log(res,'批量删除');
        if(res.message && res.message==="删除成功"){
          message.success(res.message)
          this.setState({
            refreshTable:true
          })
        }else{
          message.error(res.error || res.message)
          this.setState({
            refreshTable:true
          })
        }
      },
      formData:formData
    })
  }
  render(){
    const operations =(
      <div className='opus_tabs_select_show' style={{display:this.state.selectKeys===true? 'block' : 'none' }}>
        <span>选中<b>{this.state.selectNum}</b>个</span>
        <Button onClick={this.handleBatchDeleteSelectRows}>批量删除</Button>
      </div>
    )
    return(
      <div className='home_content_works'>
          <div className='works_title'>
            <h1>已使用：{this.state.alreadyUsedRoom}</h1>
            <Link to='/opus/upload-opus'><Button>上传作品</Button></Link>
          </div>
          <p>空间大小：如果您相信，我们可以容纳全世界。</p>
          <Tabs defaultActiveKey="1" activeKey={this.state.activeKey}
             onChange={this.handleChangeActiveKey}
             className='works_table_tabs'
             tabBarExtraContent={operations}
            >
            <TabPane tab={<p className="tabpans"><span>全部作品</span><span>({this.state.allNum})</span></p>}
              key="1">
              <Tables uri="/api/test/production" deleteUri='/api/test/delete'
                isBatchBtnShow={this.handleChangeBatchOperation}
                RefreshTableState={this.state.refreshTable}
                RunRefreshTable={this.handleChangeRefreshTableState}
                GetSelectId={this.handleGetSelectRowsId}
              />
            </TabPane>

            <TabPane tab={<p className="tabpans"><span>发布失败</span><span>({this.state.failNum})</span></p>}
              key="2">
              <Tables uri="/api/test/production" deleteUri='/api/test/delete' active="9"
                isBatchBtnShow={this.handleChangeBatchOperation}
                RefreshTableState={this.state.refreshTable}
                RunRefreshTable={this.handleChangeRefreshTableState}
                GetSelectId={this.handleGetSelectRowsId}
                />
            </TabPane>
            <TabPane tab={<p className="tabpans"><span>审核中</span><span>({this.state.auditNum})</span></p>}
              key="3">
              <Tables uri="/api/test/production" active="6" deleteUri='/api/test/delete'
                isBatchBtnShow={this.handleChangeBatchOperation}
                RefreshTableState={this.state.refreshTable}
                RunRefreshTable={this.handleChangeRefreshTableState}
                GetSelectId={this.handleGetSelectRowsId}
              />
            </TabPane>
          </Tabs>

      </div>
    )
  }
  componentDidMount(){
    Fetch.post({
      uri:'/api/test/production',
      callback:(res)=>{
        // console.log(res,'上传作品首页');
        this.setState({
          allNum:res.allProduction,
          failNum:res.failProduction,
          auditNum:res.checkingProduction,
          alreadyUsedRoom:res.sumsize
        })
      }
    })
  }
}

export default OpusTabs
