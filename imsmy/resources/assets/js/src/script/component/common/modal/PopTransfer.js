import React, { Component } from 'react'
import { Transfer, message } from 'antd'
import FetchPost from 'utils/fetch'
class PopTransfer extends Component {
  constructor(props){
    super(props)
    this.state = {
      mockData: [],
      targetKeys: []
    }
  }
  // pushData(mockData){
  //   console.log(mockData);
  //   return mockData.map((value,index)=>{
  //     return (record,index)=>{
  //       ` key:${id.toString()},
  //         title: ${record.type},
  //         description:${record.type}
  //       `}
  //   })
  // }

  filterOption(inputValue, option){
    return option.description.indexOf(inputValue) > -1;
  }

  handleChange (targetKeys, direction, moveKeys){
    // console.log(targetKeys, 'targetKeys');
    // console.log(direction, 'direction');
    // console.log(moveKeys,'moveKeys');
    if(targetKeys.length>3){
      message.error('最多选择三个分类');
      return false
    }else{
      if(this.props.handleGetId){
        this.props.handleGetId(targetKeys)
      }
      // console.log(targetKeys,'youbian');
      this.setState({ targetKeys });
    }
  }
  // render={item => item.title} //组件本身的示例
  render (){
    return(
      <div className="pop_transfer">
        <Transfer
          titles={['Source', 'Target']}
          dataSource={this.state.mockData}
          listStyle={{
          width: 172,
          height: 334,
          marginLeft: 20,
          marginRight:20
        }}
          showSearch
          searchPlaceholder={'请输入搜索内容'}
          filterOption={this.filterOption}
          targetKeys={this.state.targetKeys}
          onChange={this.handleChange.bind(this)}
          render={record => record.type}
          rowKey={record => record.id}
          />
      </div>
    )
  }
  componentDidMount() {
    FetchPost.post({
      uri:'/api/admins/fodder/fragment/gettype',
      callback:(res)=>{
        // console.log(res);
        this.setState({
          mockData:res.data
        })
      }
    })
  }

}

export default PopTransfer
