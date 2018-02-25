import React, { Component } from 'react'
import { Table, Pagination ,Icon, Spin, Button } from 'antd'
import Fetch from 'utils/fetch.js'

//表格
class TableContainer extends Component {
  constructor(props){
    super(props)
     this.state={
       loading:false,
       // stationData:[],
       data:[],
       selectedRowKeys: [],
       selectedRows:[],
     }
  }

  onSelectChange(selectedRowKeys,selectedRows){
    this.setState({ selectedRowKeys})
    //获取批量操作的表格ID
    if(this.props.onChange){
      this.props.onChange(selectedRowKeys)
    }
    //将rowKey改为record.id就可以选中它的id了
  }


  handleChangeFetch=()=>{
    this.setState({loading:true})
    Fetch.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据');
          if(res.data){
            this.setState({
              loading:false,
              data:res.data
            })
          }else if(res.tweets){
            this.setState({
              loading:false,
              data:res.tweets
            })
          }
          if(this.props.RefreshTableState===true){
            this.props.RefreshTableState = false
          }
        },
        formData:this.props.formData
      })
  }
  handleChangeFetchFormaData=()=>{
      this.setState({loading:true})
      let formData = new FormData()
      //素材页面
      if(this.props.search!=='' && this.props.search!==undefined){
        formData.append('name',this.props.search)
      }
      if(this.props.type!=='' && this.props.type!==undefined ){
        formData.append('type_id',this.props.type)
      }
      if(this.props.operator!=='' && this.props.operator!==undefined ){
        formData.append('operator_id',this.props.operator)
      }
      if(this.props.time!=='' && this.props.time!==undefined ){
        formData.append('time',this.props.time)
      }
      if(this.props.duration!=='' && this.props.duration!==undefined ){
        formData.append('duration',this.props.duration)
      }
      if(this.props.integral!=='' && this.props.integral!==undefined ){
        formData.append('integral',this.props.integral)
      }
      if(this.props.count!=='' && this.props.count!==undefined ){
        formData.append('count',this.props.count)
      }
      //用户页面
      if(this.props.userType!=='' && this.props.userType!==undefined ){
        formData.append('userType',this.props.userType)
      }
      if(this.props.thirdType!=='' && this.props.thirdType!==undefined ){
        formData.append('thirdtype',this.props.thirdType)
      }
      if(this.props.vipType!=='' && this.props.vipType!==undefined ){
        formData.append('vipLevel',this.props.vipType)
      }
      if(this.props.auditor!=='' && this.props.auditor!==undefined ){
        formData.append('checker',this.props.auditor)
      }
      if(this.props.fans!=='' && this.props.fans!==undefined ){
        formData.append('fans',this.props.fans)
      }
      if(this.props.playCount!=='' && this.props.playCount!==undefined ){
        formData.append('playCount',this.props.playCount)
      }
      if(this.props.works!=='' && this.props.works!==undefined ){
        formData.append('productionNum',this.props.works)
      }
      //数据请求
      Fetch.post({
        uri:this.props.uri,
        callback:(res)=>{
          console.log(res,'表格的数据--带参数');
          if(res.data){
            this.setState({
              loading:false,
              // stationData:res.tweets,
              data:res.data
            })
          }else if(res.tweets){
            this.setState({
              loading:false,
              // stationData:res.tweets,
              data:res.tweets
            })
          }

        },
        formData:formData
      })

    }

render() {
    const pagination ={
      showQuickJumper:true,
      showSizeChanger:true
    }
    const { selectedRowKeys, selectedRows } = this.state
    const rowSelection = {
       selectedRowKeys,
       onChange: this.onSelectChange.bind(this)
     }
    const rowKeys = selectedRowKeys.length
    return (
      <Spin spinning={this.state.loading}>
        <div className="table_container">
          <div className={(rowKeys>0) ? "tables_btn_show" : 'tables_btn_hide'}>
            <span className="tables_span">{`选中${rowKeys}个`}</span>
              { this.props.DropdownMenu }
          </div>
          <Table
            className="table_need_modify"
            rowKey={record => record.id}
            pagination={pagination}
            rowSelection={rowSelection}
            columns={this.props.columns}
            dataSource={this.state.data}  />
        </div>
      </Spin>
    );
  }
  componentDidMount(){
    this.handleChangeFetch()
  }
  componentWillReceiveProps(nextProps){
    // console.log(nextProps,'table');
    if(this.props.type !== nextProps.type || this.props.search !== nextProps.search ||
      this.props.operator !== nextProps.operator || this.props.time !== nextProps.time ||
      this.props.duration !== nextProps.duration || this.props.count !== nextProps.count ||
      this.props.integral !== nextProps.integral || this.props.userType !== nextProps.userType ||
      this.props.thirdType !== nextProps.thirdType || this.props.vipType !== nextProps.vipType ||
      this.props.auditor !== nextProps.auditor || this.props.fans !== nextProps.fans ||
      this.props.playCount !== nextProps.playCount || this.props.works !== nextProps.works
    ){
      this.props.search = nextProps.search
      this.props.type = nextProps.type
      this.props.operator = nextProps.operator
      this.props.time = nextProps.time
      this.props.duration = nextProps.duration
      this.props.count = nextProps.count
      this.props.integral = nextProps.integral //资产和下载费用
      this.props.userType = nextProps.userType
      this.props.thirdType = nextProps.thirdType
      this.props.vipType = nextProps.vipType
      this.props.auditor = nextProps.auditor
      this.props.fans = nextProps.fans
      this.props.playCount = nextProps.playCount
      this.props.works = nextProps.works
      this.handleChangeFetchFormaData()
    }
    if(this.props.RefreshTableState !== nextProps.RefreshTableState){
      this.handleChangeFetch()
    }
  }

}

export default TableContainer
